<?php

/**
 * ConfigCache.php
 *
 * PHP version 7
 *
 * Licensed under the Apache License, Version 2.0 (the "License"),
 * see LICENSE for more details: http://www.apache.org/licenses/LICENSE-2.0.
 *
 * @category PHP
 * @package  LOEYE
 * @author   Zhang Yi <loeyae@gmail.com>
 * @version  GIT: $Id: Zhang Yi $
 * @link     https://github.com/loeyae/loeye.git
 */

namespace loeye\lib;

use Symfony\Component\Cache\Adapter\{
    ApcuAdapter,
    PhpFilesAdapter
};

/**
 * ConfigCache
 *
 * @author   Zhang Yi <loeyae@gmail.com>
 */
class ConfigCache
{

    private $cache;
    private static $_instance = [];

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
     * @param type $property
     *
     * @return self
     */
    static public function getInstance($property, $type = 'config')
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

    public function getKey($bundle)
    {
        return strtr($bundle, '/', '.');
    }

    public function set($bundle, $settins, $lifeTime = null)
    {
        $key  = $this->getKey($bundle);
        $item = $this->cache->getItem($key);
        $item->set($settins);
        $item->expiresAfter($lifeTime);
        $this->cache->saveDeferred($item);
    }

    public function setMulti($values, $lifeTime = null)
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

    public function get($bundle)
    {
        $key  = $this->getKey($bundle);
        $item = $this->cache->getItem($key);
        return $item->get();
    }

    public function getMulti($bundles)
    {
        $keys  = array_map(array($this, 'getKey'), $bundles);
        $items = $this->cache->getItems($keys);
        return $items;
    }

    public function has($bundle)
    {
        $key = $this->getKey($bundle);
        return $this->cache->hasItem($key);
    }

    public function delete($bundle)
    {
        $key = $this->getKey($bundle);
        return $this->cache->deleteItem($key);
    }

}
