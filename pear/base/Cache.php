<?php

/**
 * Cache.php
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

use Symfony\Component\Cache\Adapter\{
    ApcuAdapter,
    FilesystemAdapter,
    MemcachedAdapter,
    RedisAdapter,
    ArrayAdapter,
    PhpArrayAdapter,
    PhpFilesAdapter
};

/**
 * Cache
 *
 * @author   Zhang Yi <loeyae@gmail.com>
 */
class Cache
{

    use \loeye\std\ConfigTrait;
    use \loeye\std\CacheTrait;

    const BUNDLE               = 'cache';
    const CACHE_TYPE_APC       = 'apc';
    const CACHE_TYPE_FILE      = 'file';
    const CACHE_TYPE_MEMCACHED = 'memcached';
    const CACHE_TYPE_REDIS     = 'redis';
    const CACHE_TYPE_ARRAY     = 'array';
    const CACHE_TYPE_PHP_ARRAY = 'parray';
    const CACHE_TYPE_PHP_FILE  = 'pfile';

    protected $defaultType     = self::CACHE_TYPE_FILE;
    protected $defaultLifetime = 0;
    protected $instance;
    protected static $_instance = [];

    /**
     * __construct
     *
     * @param string $type          cache type
     * @param string $key           if not file: config key; else: file index
     * @param string $key        if not file: config bundle; else: property name
     * @param string $configBaseDir if not file: config base dir; else: null
     *
     * @throws Exception
     */
    public function __construct(AppConfig $appConfig, $type = null)
    {
        $property = $appConfig->getPropertyName();
        $settins  = $appConfig->getSetting('application.cache');
        if (is_string($settins)) {
            $this->defaultType = $settins;
        } else if (is_numeric($settins)) {
            $this->defaultLifetime = int($settins);
        } else {
            $this->defaultType     = $settins['default'] ?? self::CACHE_TYPE_FILE;
            $this->defaultLifetime = $settins['lifetime'] ?? 0;
        }
        $config = $this->cacheConfig($appConfig);
        $this->_buildInstance($property, $type, $config);
    }

    /**
     * _buildInstance
     *
     * @param string                    $property property name
     * @param string                    $type     type name
     * @param \loeye\base\Configuration $config   Configuration
     *
     * @throws Exception
     */
    private function _buildInstance($property, $type, Configuration $config)
    {
        $type            = $type ?? $this->defaultType;
        $setting         = $config->get($type) ?? [];
        $namespace       = PROJECT_NAMESPACE . '.' . $property . '.' . ($setting['namespace'] ?? $type);
        $defaultLifetime = $setting['lifetime'] ?? $this->defaultLifetime;
        switch ($type) {
            case self::CACHE_TYPE_APC:
                if (!ApcuAdapter::isSupported()) {
                    throw new Exception('APCu not supported.');
                }
                $this->instance = new ApcuAdapter($namespace, $defaultLifetime);
                break;
            case self::CACHE_TYPE_MEMCACHED:
                $client         = $this->getMeachedClient($setting);
                $this->instance = new MemcachedAdapter($client, $namespace, $defaultLifetime);
                break;
            case self::CACHE_TYPE_REDIS:
                $redisClient    = $this->getRedisClient($setting);
                $this->instance = new RedisAdapter($redisClient, $namespace, $defaultLifetime);
            case self::CACHE_TYPE_ARRAY:
                $this->instance = new ArrayAdapter($this->defaultLifetime);
            case self::CACHE_TYPE_PHP_ARRAY:
                $file           = $setting['file'] ?? RUNTIME_CACHE_DIR . D_S . PROJECT_NAMESPACE . D_S . 'app.cache';
                $this->instance = new PhpArrayAdapter($file, new FilesystemAdapter($namespace, $defaultLifetime, RUNTIME_CACHE_DIR));
            case self::CACHE_TYPE_PHP_FILE:
                $directory      = $setting['directory'] ?? RUNTIME_CACHE_DIR;
                $this->instance = new PhpFilesAdapter($namespace, $defaultLifetime, $directory);
            default:
                $directory      = $setting['directory'] ?? RUNTIME_CACHE_DIR;
                $this->instance = new FilesystemAdapter($namespace, $defaultLifetime, $directory);
                break;
        }
    }


    /**
     * getInstance
     *
     * @param type $property
     *
     * @return self
     */
    static public function getInstance(AppConfig $appConfig, $type = null)
    {
        $type = $type ?? self::CACHE_TYPE_FILE;
        if (!isset(self::$_instance[$type])) {
            self::$_instance[$type] = new self($appConfig, $type);
        }
        return self::$_instance[$type];
    }

    public function __destruct()
    {
        $this->instance->commit();
    }

    public function set($key, $settins, $lifeTime = null)
    {
        $item = $this->instance->getItem($key);
        $item->set($settins);
        $item->expiresAfter($lifeTime);
        $this->instance->saveDeferred($item);
    }

    public function append($key, $settins)
    {
        $item   = $this->instance->getItem($key);
        $values = $item->get();
        $item->set(array_merge_recursive((array) $values, (array) $settins));
        $this->instance->saveDeferred($item);
    }

    public function setMulti(array $values)
    {
        $keys  = array_keys($values);
        $items = $this->instance->getItems($keys);
        foreach ($items as $item) {
            $key = $item->getKey();
            $item->set($values[$key]);
            $this->instance->saveDeferred($item);
        }
        $this->instance->commit();
    }

    public function get($key)
    {
        $item = $this->instance->getItem($key);
        return $item->get();
    }

    public function getMulti(array $keys)
    {
        $items = $this->instance->getItems($keys);
        return $items;
    }

    public function has($key)
    {
        return $this->instance->hasItem($key);
    }

    public function delete($key)
    {
        return $this->instance->deleteItem($key);
    }

    public function remove($key, $item)
    {
        $item   = $this->instance->getItem($key);
        $values = $item->get();
        if (array_key_exists($item, (array) $values)) {
            unset($values[$item]);
        } else {
            $k = array_search($item, (array) $values);
            unset($values[$k]);
        }
        if (count($values) > 0) {
            $item->set($values);
            $this->instance->saveDeferred($item);
        } else {
            $this->instance->deleteItem($key);
        }
    }

    /**
     * __call
     *
     * @param string $name      method name
     * @param mixed  $arguments argv
     *
     * @return mixed
     */
    public function __call($name, $arguments)
    {
        if (method_exists($this->instance, $name)) {
            $ref = new \ReflectionMethod($this->instance, $name);
            return $ref->invokeArgs($this->instance, $arguments);
        }
    }

    /**
     * init
     *
     * @param \loeye\base\AppConfig $appConfig appConfig
     * @param string                $type      type
     *
     * @return boolean
     */
    static public function init(AppConfig $appConfig, $type = self::CACHE_TYPE_FILE)
    {
        try {
            return self::getInstance($appConfig, $type);
        } catch (\Exception $exc) {
            Utils::errorLog($exc);
            return false;
        }
    }

}
