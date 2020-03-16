<?php

/**
 * AppConfig.php
 *
 * PHP version 7
 *
 * Licensed under the Apache License, Version 2.0 (the "License"),
 * see LICENSE for more details: http://www.apache.org/licenses/LICENSE-2.0.
 *
 * @category PHP
 * @package  LOEYE
 * @author   Zhang Yi <loeyae@gmail.com>
 * @version  2018-07-23 22:44:28
 * @link     https://github.com/loeyae/loeye2.git
 */

namespace loeye\base;

/**
 * Description of AppConfig
 *
 * @author   Zhang Yi <loeyae@gmail.com>
 */
class AppConfig implements \ArrayAccess
{

    use \loeye\std\ConfigTrait;

    const BUNDLE = 'app';

    private $_config;
    private $_propertyName;
    private $_timezone;
    private $_locale;

    /**
     * __construct
     *
     * @param string $property config $property
     */
    public function __construct($property)
    {
        $definitions   = [new \loeye\config\app\ConfigDefinition(), new \loeye\config\app\DeltaDefinition()];
        $configuration = $this->propertyConfig($property, self::BUNDLE, $definitions);
        $this->processConfiguration($configuration);
        $this->_propertyName = $property;
    }

    /**
     * processConfiguration
     *
     * @param \loeye\base\Configuration $configuration
     */
    protected function processConfiguration(Configuration $configuration)
    {
        $masterConfig = $configuration->getConfig();
        $profile      = $configuration->get('profile');
        $deltaConfig  = [];
        if ($profile) {
            $deltaConfig = $configuration->getConfig(null, ['profile' => $profile]) ?? [];
        }
        $this->mergConfiguration($masterConfig, $deltaConfig);
    }

    /**
     * mergConfiguration
     *
     * @param array $mater
     * @param array $delta
     */
    protected function mergConfiguration(array $mater, array $delta)
    {
        foreach ($delta as $key => $value) {
            if ($value) {
                $mater[$key] = $value;
            }
        }
        $this->_config = $mater;
    }

    /**
     * offsetExists
     *
     * @param mixed $offset offset
     *
     * @return boolean
     */
    public function offsetExists($offset)
    {
        switch ($offset) {
            case 'property_name':
            case 'timezone':
            case 'base_dir':
                return true;
            default :
                if (isset($this->_config[$offset])) {
                    return true;
                }
        }
        return false;
    }

    /**
     * offsetGet
     *
     * @param mixed $offset offset
     *
     * @return mixed
     */
    public function offsetGet($offset)
    {
        switch ($offset) {
            case 'property_name':
                return $this->getPropertyName();
            case 'timezone':
                return $this->getTimezone();
            case 'base_dir':
                return $this->getPropertyConfigBaseDir();
            default :
                return $this->getSetting($offset);
        }
    }

    /**
     * offsetSet
     *
     * @param mixed $offset offset
     * @param mixed $value  value
     *
     * @return void
     */
    public function offsetSet($offset, $value)
    {
        switch ($offset) {
            case 'property_name':
                $this->setPropertyName($value);
                break;
            case 'timezone':
                $this->setTimezone($value);
                break;
            case 'base_dir':
                $this->setPropertyConfigBaseDir($value);
                break;
            default :
                break;
        }
    }

    /**
     * offsetUnset
     *
     * @param mixed $offset offset
     *
     * @return void
     */
    public function offsetUnset($offset)
    {
        return;
    }

    /**
     * getSetting
     *
     * @param string $key     ex: key|key1.key2
     * @param mixed  $default default value
     *
     * @return mixed
     * @throws Exception
     */
    public function getSetting($key, $default = null)
    {
        if (empty($key)) {
            throw new \loeye\error\BusinessException(\loeye\error\BusinessException::INVALID_CONFIG_SET_MSG, \loeye\error\BusinessException::INVALID_CONFIG_SET_CODE, ["setting" => "setting " . $key]);
        }
        $keyList = explode(".", $key);
        $config  = $this->_config;
        foreach ($keyList as $key) {
            if (isset($config[$key])) {
                $config = $config[$key];
            } else {
                return $default;
            }
        }
        return $config;
    }

    /**
     * setPropertyName
     *
     * @param string $propertyName property name
     *
     * @return void
     */
    public function setPropertyName($propertyName)
    {
        $this->_propertyName = $propertyName;
    }

    /**
     * getPropertyName
     *
     * @return string
     * @throws Exception
     */
    public function getPropertyName()
    {
        if (!empty($this->_propertyName)) {
            return $this->_propertyName;
        }
        $propertyName = $this->getSetting('configuration.property_name');
        if (!empty($propertyName)) {
            return $propertyName;
        }
        return null;
    }

    /**
     * setTimezone
     *
     * @param string $timezone timezone
     *
     * @return void
     */
    public function setTimezone($timezone)
    {
        $this->_timezone = $timezone;
    }

    /**
     * getTimezone
     *
     * @return string
     */
    public function getTimezone()
    {
        if (!empty($this->_timezone)) {
            return $this->_timezone;
        }
        $timezone     = $this->getSetting('configuration.timezone');
        $timezoneList = timezone_identifiers_list();
        if (!empty($timezone) && in_array($timezone, $timezoneList)) {
            return $timezone;
        }
        return 'UTC';
    }

    /**
     * setLocale
     *
     * @param string $locale locale
     *
     * @return void
     */
    public function setLocale($locale)
    {
        $supported = (array) $this->getSetting('locale.supported_languages', ['zh_CN']);
        if (in_array($locale, $supported)) {
            $this->_locale = $locale;
        }
    }

    /**
     * getLocale
     *
     * @return string
     */
    public function getLocale()
    {
        if (!empty($this->_locale)) {
            return $this->_locale;
        }
        $locale    = $this->getSetting('locale.default');
        $supported = (array) $this->getSetting('locale.supported_languages', ['zh_CN']);
        return in_array($locale, $supported) ? $locale : $supported[0];
    }
    
    /**
     * getActiveProfile
     * 
     * @return string
     */
    public function getActiveProfile()
    {
        return $this->getSetting("profile");
    }

}
