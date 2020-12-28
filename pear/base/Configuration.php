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

use InvalidArgumentException;
use loeye\config\ConfigurationLoader;
use loeye\config\general\DeltaConfigDefinition;
use loeye\config\general\RulesetConfigDefinition;
use loeye\config\module\ConfigDefinition;
use loeye\lib\Secure;
use Symfony\Component\Config\Definition\ConfigurationInterface;

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
    protected $hash;

    public const ENV_TAG = '${';

    /**
     * __construct
     *
     * @param string $property property
     * @param string $bundle bundle
     * @param mixed $definition definition
     * @param string $context context
     *
     * @param null $baseDir
     * @param null $cacheDir
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
            $rulesetDefinition  = new RulesetConfigDefinition();
            $deltaDefinition   = new DeltaConfigDefinition();
            $this->_definition = [$rulesetDefinition, $deltaDefinition];
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
    public function getBaseDir(): string
    {
        return $this->_baseDir;
    }

    /**
     * getCurrentBundle
     *
     * @return string
     */
    public function getBundle(): string
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
    public function setDefinition($definition): void
    {
        if (is_array($definition)) {
            foreach ($definition as $value) {
                if (!($value instanceof ConfigurationInterface)) {
                    throw new InvalidArgumentException('definition must be instance of \Symfony\Component\Config\ConfigCacheInterface');
                }
            }
            $this->_definition = $definition;
        } elseif ($definition instanceof ConfigurationInterface) {
            $this->_definition = [$definition];
        } else {
            throw new InvalidArgumentException('definition must be instance of \Symfony\Component\Config\ConfigCacheInterface');
        }
    }

    /**
     *
     * @return array of \Symfony\Component\Config\ConfigCacheInterface's instance
     */
    public function getDefinition(): array
    {
        return $this->_definition;
    }

    /**
     * bundle
     *
     * @param string $bundle  bundle
     * @param string $context context
     *
     * @return void
     */
    public function bundle($bundle, $context = null): void
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
    public function context($context = null): void
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
        $keyList = explode('.', $key);

        $config = $this->_config;
        foreach ($keyList as $k) {
            $config = $this->_getConfig($k, $config);
            if (null === $config) {
                return $default;
            }
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
     * @return array
     */
    public function getSettings($bundle = null): array
    {
        if (!empty($bundle)) {
            $this->bundle($bundle, null);
        }
        return $this->_config;
    }

    /**
     * merge
     *
     * @param array $config config settings
     * @param bool $replace is replace exists key
     */
    public function merge(array $config, $replace = false): void
    {
        foreach ($config as $key => $value) {
            if ($value) {
                if ($replace) {
                    $this->_config[$key] = $value;
                } else if (!isset($this->_config[$key]) || $this->_config[$key] === null) {
                    $this->_config[$key] = $value;
                } else if (is_array($value) || is_array($this->_config[$key])) {
                    $this->_config[$key] = $this->mergeConfiguration($this->_config[$key], $value);
                } else {
                    $this->_config[$key] = $value;
                }
            }
        }
    }

    /**
     * mergeConfiguration
     *
     * @param array $mater
     * @param array $delta
     * @return array
     */
    protected function mergeConfiguration(array $mater, array $delta): array
    {
        foreach ($delta as $key => $value) {
            if ($value) {
                if (is_array($value) && isset($mater[$key]) && is_array($mater[$key])) {
                    $mater[$key] = $this->mergeConfiguration($mater[$key], $value);
                } else {
                    $mater[$key] = $value;
                }
            }
        }
        return $mater;
    }

    /**
     *
     * @return string
     */
    private function _computeHash(): string
    {
        return Secure::getKey([$this->getBundle(), $this->getContext()]);
    }

    /**
     * isFresh
     *
     * @return boolean
     */
    public function isFresh(): bool
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
    private function _loadConfig(): void
    {
        if ($this->isFresh()) {
            $bundle  = $this->getBundle();
            $context = $this->getContext();
            if (is_string($context)) {
                $array   = explode('=', $context);
                $context = array_combine([$array[0]], [$array[1]]);
            }
            $namespace = strtr($bundle, ['/' => '.', '\\' => '.']);
            $loader    = new ConfigurationLoader($this->_baseDir, $namespace, $this->getDefinition(), true,
                $this->_cacheDir);
            if (current($this->_definition) instanceof ConfigDefinition) {
                $this->_config = $loader->loadModules();
            } else {
                $this->_config = $loader->load($context);
            }
        }
    }

    /**
     * _getConfig
     *
     * @param string $key key
     * @param array $config config
     * @return mixed
     */
    private function _getConfig($key, $config)
    {
        return isset($config[$key]) ? $this->getEnv($config[$key]) : null;
    }

    /**
     * 获取环境变量
     *
     * @param string $var
     * @return mixed
     */
    private function getEnv($var)
    {
        if (is_iterable($var)) {
            return array_map(array($this, 'getEnv'), (array)$var);
        }
        if ($var && Utils::startWith($var, self::ENV_TAG)) {
            $l          = mb_strlen(self::ENV_TAG);
            $envSetting = mb_substr($var, $l, - 1);
            $envArray   = explode(':', $envSetting);
            $key        = array_shift($envArray);
            $default    = count($envArray) > 0 ? implode(':', $envArray) : null;
            if ($value = getenv($key)) {
                return $value;
            }
            return $_SERVER[$key] ?? $_ENV[$key] ?? $default;
        }
        return $var;
    }

}
