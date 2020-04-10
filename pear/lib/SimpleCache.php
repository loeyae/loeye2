<?php

/**
 * SimpleCache.php
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

namespace loeye\lib;

use Symfony\Component\Cache\Adapter\{AdapterInterface, ApcuAdapter, PhpFilesAdapter};
use Psr\Cache\InvalidArgumentException;
use Symfony\Component\Cache\CacheItem;
use Symfony\Component\Cache\Exception\CacheException;
use Traversable;

/**
 * SimpleCache
 *
 * @author   Zhang Yi <loeyae@gmail.com>
 */
class SimpleCache
{

    /**
     * @var AdapterInterface
     */
    private $cache;
    private static $_instance = [];

    /**
     * SimpleCache constructor.
     * @param $property
     * @param string $type
     * @throws CacheException
     */
    public function __construct($property, $type = 'config')
    {
        $namespace       = PROJECT_NAMESPACE . '.' . $property . '.' . $type;
        $defaultLifetime = defined('LOEYE_MODE') && LOEYE_MODE == LOEYE_MODE_PROD ? 0 : 30;
        if (ApcuAdapter::isSupported()) {
            $version     = null;
            $this->cache = new ApcuAdapter($namespace, $defaultLifetime, $version);
        } else {
            $directory   = RUNTIME_CACHE_DIR;
            $this->cache = new PhpFilesAdapter($namespace, $defaultLifetime, $directory);
        }
    }

    /**
     * getInstance
     *
     * @param string $property
     * @param string $type
     *
     * @return self
     * @throws CacheException
     */
    public static function getInstance($property, $type = 'config'): self
    {
        if (!isset(self::$_instance[$property])) {
            self::$_instance[$property] = new self($property, $type);
        }
        return self::$_instance[$property];
    }

    public function __destruct()
    {
        $this->cache->commit();
    }

    /**
     * getKey
     *
     * @param $bundle
     * @return string
     */
    public function getKey($bundle): string
    {
        return str_replace('/', '.', $bundle);
    }

    /**
     * set
     *
     * @param $bundle
     * @param $settings
     * @param null $lifeTime
     * @throws InvalidArgumentException
     */
    public function set($bundle, $settings, $lifeTime = null): void
    {
        $key  = $this->getKey($bundle);
        $item = $this->cache->getItem($key);
        $item->set($settings);
        $item->expiresAfter($lifeTime);
        $this->cache->saveDeferred($item);
    }

    /**
     * setMulti
     *
     * @param $values
     * @param null $lifeTime
     * @throws InvalidArgumentException
     */
    public function setMulti($values, $lifeTime = null): void
    {
        $keys = [];
        $vs   = [];
        foreach ($values as $bundle => $value) {
            $key      = $this->getKey($bundle);
            $vs[$key] = $value;
        }
        $items = $this->cache->getItems($keys);
        foreach ($items as $item) {
            $key = $item->getKey();
            $item->set($vs[$key]);
            $item->expiresAfter($lifeTime);
            $this->cache->saveDeferred($item);
        }
        $this->cache->commit();
    }

    /**
     * get
     *
     * @param $bundle
     * @return mixed
     * @throws InvalidArgumentException
     */
    public function get($bundle)
    {
        $key  = $this->getKey($bundle);
        $item = $this->cache->getItem($key);
        return $item->get();
    }

    /**
     * getMulti
     *
     * @param $bundles
     * @return iterable|CacheItem[]|Traversable
     * @throws InvalidArgumentException
     */
    public function getMulti($bundles)
    {
        $keys  = array_map(array($this, 'getKey'), $bundles);
        return $this->cache->getItems($keys);
    }

    /**
     * has
     *
     * @param $bundle
     * @return bool
     * @throws InvalidArgumentException
     */
    public function has($bundle): bool
    {
        $key = $this->getKey($bundle);
        return $this->cache->hasItem($key);
    }

    /**
     * delete
     *
     * @param $bundle
     * @return bool
     * @throws InvalidArgumentException
     */
    public function delete($bundle): bool
    {
        $key = $this->getKey($bundle);
        return $this->cache->deleteItem($key);
    }

    /**
     * compress
     *
     * @param mixed $data   data
     * @param bool  $encode is encode
     *
     * @return mixed
     */
    public static function compress($data, $encode = true)
    {
        if ($encode) {
            if (extension_loaded('zlib')) {
                return zlib_encode(serialize($data), ZLIB_ENCODING_GZIP, 9);
            }
            return serialize($data);
        } else {
            if (extension_loaded('zlib')) {
                return unserialize(zlib_decode($data), null);
            }
            return unserialize($data, null);
        }
    }

}
