<?php

/**
 * Configuration.php
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
 * Description of Configuration
 *
 * @author   Zhang Yi <loeyae@gmail.com>
 */
class Configuration
{

    protected $property;
    private $_baseDir;
    private $_baseBundle;
    private $_baseContext;
    private $_bundle;
    private $_context;
    private $_config;
    private $_definition;
    private $_cacheDir;
    protected $hash = null;

    const ENV_TAG = '${';

    /**
     * __construct
     *
     * @param string $property property
     * @param string $bundle   bundle
     * @param string $context  context
     *
     * @return void
     */
    public function __construct($property, $bundle, $definition = null, $context = null, $baseDir = null, $cacheDir = null)
    {
        $this->property = $property;
        if (null === $baseDir) {
            $baseDir = PROJECT_CONFIG_DIR;
        }
        $this->_baseDir = $baseDir . DIRECTORY_SEPARATOR . $property;
        if (null === $cacheDir) {
            $cacheDir = RUNTIME_CACHE_DIR;
        }
        $this->_cacheDir    = $cacheDir . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . $property;
        $this->_baseBundle  = $bundle;
        $this->_baseContext = $context;
        if (null === $definition) {
            $ruseltDefinition  = new \loeye\config\general\RulesetConfigDefinition();
            $deltaDefinition   = new \loeye\config\general\DeltaConfigDefinition();
            $this->_definition = [$ruseltDefinition, $deltaDefinition];
        } else {
            $this->setDefinition($definition);
        }
        $this->bundle($bundle, $context);
    }

    /**
     * getBaseDir
     *
     * @return string
     */
    public function getBaseDir()
    {
        return $this->_baseDir;
    }

    /**
     * getCurrentBundle
     *
     * @return string
     */
    public function getBundle()
    {
        return $this->_bundle ?? $this->_baseBundle;
    }

    /**
     * getContext
     *
     * @return mixed
     */
    public function getContext()
    {
        return $this->_context ?? $this->_baseContext;
    }

    /**
     * setDefinition
     *
     * @param mixed $definition
     */
    public function setDefinition($definition)
    {
        if (is_array($definition)) {
            foreach ($definition as $value) {
                if (!($value instanceof \Symfony\Component\Config\Definition\ConfigurationInterface)) {
                    throw \InvalidArgumentException('definition must be instance of \Symfony\Component\Config\ConfigCacheInterface');
                }
            }
            $this->_definition = $definition;
        } elseif ($definition instanceof \Symfony\Component\Config\Definition\ConfigurationInterface) {
            $this->_definition = [$definition];
        } else {
            throw \InvalidArgumentException('definition must be instance of \Symfony\Component\Config\ConfigCacheInterface');
        }
    }

    /**
     *
     * @return array of \Symfony\Component\Config\ConfigCacheInterface's instance
     */
    public function getDefinition()
    {
        return $this->_definition;
    }

    /**
     * bundle
     *
     * @param string $bundle  boudle
     * @param string $context context
     *
     * @return mixed
     */
    public function bundle($bundle, $context = null)
    {
        $this->_bundle = $bundle;
        $this->context($context);
    }

    /**
     * context
     *
     * @param string $context context
     *
     * @return void
     */
    public function context($context = null)
    {
        $this->_context = $context;
        $this->_loadConfig();
    }

    /**
     * get
     *
     * @param string $key     key
     * @param string $default default
     *
     * @return null
     */
    public function get($key, $default = null)
    {
        $keyList = explode(".", $key);

        $config = $this->_config;
        foreach ($keyList as $k) {
            $config = $this->_getConfig($k, $config);
            if (null === $config) {
                return $default;
            }
            $isList = false;
        }
        return $config;
    }

    /**
     * getConfig
     *
     * @param string $bundle  bundle
     * @param string $context context
     *
     * @return mixed
     */
    public function getConfig($bundle = null, $context = null)
    {
        if (!empty($bundle)) {
            $this->bundle($bundle, $context);
        } else if (!empty($context)) {
            $this->context($context);
        }
        return $this->_config;
    }

    /**
     * getSettings
     *
     * @param string $bundle bundle
     * @param bool   $reduce need reduce
     *
     * @return array
     */
    public function getSettings($bundle = null, $reduce = true)
    {
        if (!empty($bundle)) {
            $this->bundle($bundle, null);
        }
        return $this->_config;
    }

    /**
     *
     * @return string
     */
    private function _computeHash()
    {
        return \loeye\lib\Secure::getKey([$this->getBundle(), $this->getContext()]);
    }

    /**
     * isFresh
     *
     * @return boolean
     */
    public function isFresh()
    {
        $hash = $this->_computeHash();
        if (null === $this->hash) {
            $this->hash = $hash;
            return true;
        }
        return $hash !== $this->hash;
    }

    /**
     * _loadConfig
     */
    private function _loadConfig()
    {
        if ($this->isFresh()) {
            $bundle  = $this->getBundle();
            $context = $this->getContext();
            if (is_string($context)) {
                $array   = explode('=', $context);
                $context = array_combine([$array[0]], [$array[1]]);
            }
            $namespace = strtr($bundle, ['/' => '.', '\\' => '.']);
            $loader    = new \loeye\config\ConfigurationLoader($this->_baseDir, $namespace, $this->getDefinition(), true, $this->_cacheDir);
            if (current($this->_definition) instanceof \loeye\config\module\ConfigDefinition) {
                $this->_config = $loader->loadModules();
            } else {
                $this->_config = $loader->load($context);
            }
        }
    }

    /**
     * _getConfig
     *
     * @param string $key    key
     * @param array  $config config
     * @param bool   $isList is list
     *
     * @return mixed
     */
    private function _getConfig($key, $config, $isList = false)
    {
        return isset($config[$key]) ? $this->getEnv($config[$key]) : null;
    }

    /**
     * 获取环境变量
     *
     * @param type $var
     * @return type
     */
    private function getEnv($var)
    {
        if (is_iterable($var)) {
            return array_map(array($this, "getEnv"), $var);
        }
        if ($var && Utils::startwith($var, self::ENV_TAG)) {
            $l          = mb_strlen(self::ENV_TAG);
            $envSetting = mb_substr($var, $l, - 1);
            $envArray   = explode(':', $envSetting);
            $key        = array_shift($envArray);
            $default    = count($envArray) > 0 ? implode(':', $envArray) : null;
            return getenv($key) ?: (isset($_ENV[$key]) ? $_ENV[$key] : $default);
        }
        return $var;
    }

}
