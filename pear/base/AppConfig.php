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

use ArrayAccess;
use loeye\config\app\ConfigDefinition;
use loeye\config\app\DeltaDefinition;
use loeye\error\BusinessException;
use loeye\std\ConfigTrait;

/**
 * Description of AppConfig
 *
 * @author  Zhang Yi <loeyae@gmail.com>
 */
class AppConfig implements ArrayAccess
{

    use ConfigTrait;

    public const BUNDLE = 'app';

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
        $definitions = [new ConfigDefinition(), new DeltaDefinition()];
        $configuration = $this->propertyConfig($property, self::BUNDLE, $definitions);
        $this->processConfiguration($configuration);
        $this->_propertyName = $property;
    }

    /**
     * processConfiguration
     *
     * @param Configuration $configuration
     */
    protected function processConfiguration(Configuration $configuration): void
    {
        $profile = $configuration->get('profile');
        $cloneConfig = clone $configuration;
        $deltaConfig = [];
        if ($profile) {
            $deltaConfig = $cloneConfig->getConfig(null, ['profile' => $profile]) ?? [];
        }
        $configuration->merge($deltaConfig);
        $this->_config = $configuration->getConfig();
    }

    /**
     * offsetExists
     *
     * @param mixed $offset offset
     *
     * @return boolean
     */
    public function offsetExists($offset): bool
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
            default :
                return $this->getSetting($offset);
        }
    }

    /**
     * offsetSet
     *
     * @param mixed $offset offset
     * @param mixed $value value
     *
     * @return void
     */
    public function offsetSet($offset, $value): void
    {
        switch ($offset) {
            case 'property_name':
                $this->setPropertyName($value);
                break;
            case 'timezone':
                $this->setTimezone($value);
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
    public function offsetUnset($offset): void
    {
    }

    /**
     * getSetting
     *
     * @param string $key ex: key|key1.key2
     * @param mixed $default default value
     *
     * @return mixed
     */
    public function getSetting($key, $default = null)
    {
        if (empty($key)) {
            Logger::trace(BusinessException::INVALID_CONFIG_SET_MSG,
                BusinessException::INVALID_CONFIG_SET_CODE, __FILE__, __LINE__);
            return null;
        }
        $keyList = explode('.', $key);
        $config = $this->_config;
        foreach ($keyList as $k) {
            if (isset($config[$k])) {
                $config = $config[$k];
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
    public function setPropertyName($propertyName): void
    {
        $this->_propertyName = $propertyName;
    }

    /**
     * getPropertyName
     *
     * @return string
     */
    public function getPropertyName(): ?string
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
    public function setTimezone($timezone): void
    {
        $this->_timezone = $timezone;
    }

    /**
     * getTimezone
     *
     * @return string
     */
    public function getTimezone(): string
    {
        if (!empty($this->_timezone)) {
            return $this->_timezone;
        }
        $timezone = $this->getSetting('configuration.timezone');
        $timezoneList = timezone_identifiers_list();
        if (!empty($timezone) && in_array($timezone, $timezoneList, true)) {
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
    public function setLocale($locale): void
    {
        $supported = (array)$this->getSetting('locale.supported_languages', ['zh_CN']);
        if (in_array($locale, $supported, true)) {
            $this->_locale = $locale;
        }
    }

    /**
     * getLocale
     *
     * @return string
     */
    public function getLocale(): string
    {
        if (!empty($this->_locale)) {
            return $this->_locale;
        }
        $locale = $this->getSetting('locale.default');
        $supported = (array)$this->getSetting('locale.supported_languages', ['zh_CN']);
        return in_array($locale, $supported, true) ? $locale : $supported[0];
    }

    /**
     * getActiveProfile
     *
     * @return string
     */
    public function getActiveProfile(): string
    {
        return $this->getSetting('profile');
    }

}
