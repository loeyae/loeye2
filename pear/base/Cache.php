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

use loeye\std\CacheTrait;
use loeye\std\ConfigTrait;
use Psr\Cache\InvalidArgumentException;
use ReflectionException;
use ReflectionMethod;
use Symfony\Component\Cache\Adapter\{AdapterInterface,
    ApcuAdapter,
    ArrayAdapter,
    FilesystemAdapter,
    MemcachedAdapter,
    PhpArrayAdapter,
    PhpFilesAdapter,
    RedisAdapter};
use Symfony\Component\Cache\CacheItem;
use Symfony\Component\Cache\Exception\CacheException;
use Traversable;

/**
 * Cache
 *
 * @author   Zhang Yi <loeyae@gmail.com>
 */
class Cache
{

    use ConfigTrait;
    use CacheTrait;

    public const BUNDLE = 'cache';
    public const CACHE_TYPE_APC = 'apc';
    public const CACHE_TYPE_FILE = 'file';
    public const CACHE_TYPE_MEMCACHED = 'memcached';
    public const CACHE_TYPE_REDIS = 'redis';
    public const CACHE_TYPE_ARRAY = 'array';
    public const CACHE_TYPE_PHP_ARRAY = 'parray';
    public const CACHE_TYPE_PHP_FILE = 'pfile';

    protected $defaultType = self::CACHE_TYPE_FILE;
    protected $defaultLifetime = 0;

    /**
     * @var AdapterInterface
     */
    protected $instance;
    protected static $_instance = [];

    /**
     * __construct
     *
     * @param AppConfig $appConfig
     * @param string $type cache type
     * @throws Exception
     * @throws CacheException
     */
    public function __construct(AppConfig $appConfig, $type = null)
    {
        $property = $appConfig->getPropertyName();
        $settings = $appConfig->getSetting('application.cache');
        if (is_string($settings)) {
            $this->defaultType = $settings;
        } else if (is_numeric($settings)) {
            $this->defaultLifetime = (int)$settings;
        } else {
            $this->defaultType = $settings['default'] ?? self::CACHE_TYPE_FILE;
            $this->defaultLifetime = $settings['lifetime'] ?? 0;
        }
        $config = $this->cacheConfig($appConfig);
        $this->_buildInstance($property, $type, $config);
    }

    /**
     * _buildInstance
     *
     * @param string $property property name
     * @param string $type type name
     * @param Configuration $config Configuration
     *
     * @throws Exception
     * @throws CacheException
     */
    private function _buildInstance($property, $type, Configuration $config): void
    {
        $type = $type ?? $this->defaultType;
        $setting = $config->get($type) ?? [];
        $namespace = PROJECT_NAMESPACE . '.' . $property . '.' . ($setting['namespace'] ?? $type);
        $defaultLifetime = $setting['lifetime'] ?? $this->defaultLifetime;
        switch ($type) {
            case self::CACHE_TYPE_APC:
                if (!ApcuAdapter::isSupported()) {
                    throw new Exception('APCu not supported.');
                }
                $this->instance = new ApcuAdapter($namespace, $defaultLifetime);
                break;
            case self::CACHE_TYPE_MEMCACHED:
                $client = $this->getMemcachedClient($setting);
                $this->instance = new MemcachedAdapter($client, $namespace, $defaultLifetime);
                break;
            case self::CACHE_TYPE_REDIS:
                $redisClient = $this->getRedisClient($setting);
                $this->instance = new RedisAdapter($redisClient, $namespace, $defaultLifetime);
                break;
            case self::CACHE_TYPE_ARRAY:
                $this->instance = new ArrayAdapter($this->defaultLifetime);
                break;
            case self::CACHE_TYPE_PHP_ARRAY:
                $file = $setting['file'] ?? RUNTIME_CACHE_DIR . D_S . PROJECT_NAMESPACE . D_S . 'app.cache';
                $this->instance = new PhpArrayAdapter($file, new FilesystemAdapter($namespace, $defaultLifetime, RUNTIME_CACHE_DIR));
                break;
            case self::CACHE_TYPE_PHP_FILE:
                $directory = $setting['directory'] ?? RUNTIME_CACHE_DIR;
                $this->instance = new PhpFilesAdapter($namespace, $defaultLifetime, $directory);
                break;
            default:
                $directory = $setting['directory'] ?? RUNTIME_CACHE_DIR;
                $this->instance = new FilesystemAdapter($namespace, $defaultLifetime, $directory);
                break;
        }
    }


    /**
     * getInstance
     *
     * @param AppConfig $appConfig app config
     * @param string|null $type type
     * @return self
     * @throws Exception
     * @throws CacheException
     */
    public static function getInstance(AppConfig $appConfig, $type = null): self
    {
        $sType = $type ?? 'default';
        if (!isset(self::$_instance[$sType])) {
            self::$_instance[$sType] = new self($appConfig, $type);
        }
        return self::$_instance[$sType];
    }

    /**
     * @param string $key cache key
     * @param mixed $settings cache value
     * @param int|null $lifeTime expire time
     *
     * @return void
     */
    public function set($key, $settings, $lifeTime = null): void
    {
        try {
            $item = $this->instance->getItem($key);
            $item->set($settings);
            $item->expiresAfter($lifeTime);
            $this->instance->saveDeferred($item);
            $this->instance->commit();
        } catch (InvalidArgumentException $e) {
            Utils::errorLog($e);
        }
    }

    /**
     * append
     *
     * @param string $key cache key
     * @param mixed $settings cache value
     *
     * @return void
     */
    public function append($key, $settings): void
    {
        try {
            $item = $this->instance->getItem($key);
            $values = $item->get();
            $item->set(array_merge_recursive((array)$values, (array)$settings));
            $this->instance->saveDeferred($item);
            $this->instance->commit();
        } catch (InvalidArgumentException $e) {
            Utils::errorLog($e);
        }
    }

    /**
     * setMulti
     *
     * @param array $values array of cache data
     * @return void
     * @throws InvalidArgumentException
     *
     */
    public function setMulti(array $values): void
    {
        $keys = array_keys($values);
        $items = $this->instance->getItems($keys);
        foreach ($items as $item) {
            $key = $item->getKey();
            $item->set($values[$key]);
            $this->instance->saveDeferred($item);
        }
        $this->instance->commit();
    }

    /**
     * get
     *
     * @param string $key cache key
     *
     * @return mixed
     * @throws InvalidArgumentException
     */
    public function get($key)
    {
        $item = $this->instance->getItem($key);
        return $item->get();
    }

    /**
     * getMulti
     *
     * @param array $keys array of cache key
     *
     * @return CacheItem[]|Traversable
     * @throws InvalidArgumentException
     */
    public function getMulti(array $keys)
    {
        return $this->instance->getItems($keys);
    }

    /**
     * has
     *
     * @param string $key cache key
     *
     * @return bool
     * @throws InvalidArgumentException
     */
    public function has($key): bool
    {
        return $this->instance->hasItem($key);
    }

    /**
     * delete
     *
     * @param string $key cache key
     *
     * @return bool
     * @throws InvalidArgumentException
     */
    public function delete($key): bool
    {
        return $this->instance->deleteItem($key);
    }

    /**
     * remove
     *
     * @param string $key cache key
     * @param $item
     * @throws InvalidArgumentException
     */
    public function remove($key, $item): void
    {
        $cacheItem = $this->instance->getItem($key);
        $values = $cacheItem->get();
        if (array_key_exists($item, (array)$values)) {
            unset($values[$item]);
        } else {
            $k = array_search($item, (array)$values, true);
            unset($values[$k]);
        }
        if (count($values) > 0) {
            $cacheItem->set($values);
            $this->instance->saveDeferred($cacheItem);
        } else {
            $this->instance->deleteItem($key);
        }
    }

    /**
     * __call
     *
     * @param string $name method name
     * @param mixed $arguments argv
     *
     * @return mixed
     * @throws ReflectionException
     */
    public function __call($name, $arguments)
    {
        if (method_exists($this->instance, $name)) {
            $ref = new ReflectionMethod($this->instance, $name);
            return $ref->invokeArgs($this->instance, $arguments);
        }
        return null;
    }

    /**
     * init
     *
     * @param AppConfig $appConfig appConfig
     * @param string $type type
     *
     * @return mixed
     */
    public static function init(AppConfig $appConfig, $type = null)
    {
        try {
            return self::getInstance($appConfig, $type);
        } catch (\Exception $exc) {
            Utils::errorLog($exc);
            return false;
        }
    }

}
