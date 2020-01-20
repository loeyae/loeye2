<?php

/**
 * ConfigurationLoader.php
 *
 * PHP version 7
 *
 * Licensed under the Apache License, Version 2.0 (the "License"),
 * see LICENSE for more details: http://www.apache.org/licenses/LICENSE-2.0.
 *
 * @category PHP
 * @package  LOEYE
 * @author   Zhang Yi <loeyae@gmail.com>
 * @version  2020年1月17日 下午8:51:43
 * @link     https://github.com/loeyae/loeye.git
 */

namespace loeye\config;

use \Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * ConfigurationLoader
 *
 * @author   Zhang Yi <loeyae@gmail.com>
 */
class ConfigurationLoader {

    /**
     *
     * @var string
     */
    protected $directory;

    /**
     *
     * @var string
     */
    protected $namespace;

    /**
     *
     * @var boolean
     */
    protected $cacheable;

    /**
     *
     * @var ConfigurationInterface
     */
    protected $definition;
    protected $cacheDirectory;

    /**
     *
     * @var ConfigCache
     */
    protected $configCache;


    /**
     *
     * @param string                            $directory      配置文件基础目录
     * @param string                            $namespace      配置文件名称空间
     * @param array|ConfigurationInterface|null $definition     配置文件规则实例
     * @param boolean                           $cacheable      是否缓存
     * @param string                            $cacheDirectory 缓存目录
     */
    public function __construct($directory, $namespace, $definition = null, $cacheable = true, $cacheDirectory = null)
    {
        $this->directory = $directory;
        $this->namespace = $namespace;
        if (null === $definition) {
            $definition = new general\RulesetConfigDefinition();
        }
        $this->definition = $definition;
        $this->cacheable  = $cacheable;
        if ($this->cacheable) {
            if (null === $cacheDirectory) {
                $this->cacheDirectory = RUNTIME_CACHE_DIR . D_S .PROJECT_NAMESPACE .D_S . 'config';
                !defined('PROJECT_PROPERTY') ?? $this->cacheDirectory . DIRECTORY_SEPARATOR . PROJECT_PROPERTY;
            } else {
                $this->cacheDirectory = $cacheDirectory;
            }
            $this->configCache = new ConfigCache($directory, $this->cacheDirectory, $namespace);
        }
    }


    /**
     * getDirectory
     *
     * @return string
     */
    public function getDirectory()
    {
        return $this->directory;
    }


    /**
     * load
     *
     * @param array|string|null $context
     *
     * @return array
     */
    public function load($context = null)
    {
        if (null === $context) {
            $context = Processor::DEFAULT_SETTINGS;
        }
        if ($this->cacheable && !$this->configCache->isFresh()) {
            return $this->getCache($context);
        }
        $locator = new FileLocator($this->directory);
        $loader  = new YamlFileLoader($locator);
        $loader->setCurrentDir($this->nsToPath($this->namespace));
        $configs = $loader->import('*.yml');
        if (count($configs) > 1) {
            $configs = array_reduce($configs, function($carry, $item) {
                if ($carry) {
                    $carry = array_merge($carry, $item);
                } else {
                    $carry = $item;
                }
                return $carry;
            });
        }
        $process    = new Processor();
        $definition = $this->definition;
        if (!is_array($definition)) {
            $definition = [$definition];
        }
        $configs = $process->processConfigurations($definition, $configs);
        if ($this->cacheable) {
            $this->setCache($configs);
        }
        if (is_string($context)) {
            return isset($configs[$context]) ? $configs[$context] : null;
        }
        if (is_array($context)) {
            $current = each($context);
            $config  = isset($configs[$current['key']]) ? $configs[$current['key']] : array();
            if(empty($config)) {
                return null;
            }
            return isset($config[$current['value']]) ? $config[$current['value']] : null;
        }
    }


    /**
     * loadModules
     *
     * @param array|string|null $context
     *
     * @return array
     */
    public function loadModules($context = null)
    {
        if (null === $context) {
            $context = Processor::DEFAULT_SETTINGS;
        }
        if ($this->cacheable && !$this->configCache->isFresh()) {
            return $this->getCache($context);
        }
        $locator = new FileLocator($this->directory);
        $loader  = new YamlFileLoader($locator);
        $loader->setCurrentDir($this->nsToPath($this->namespace));
        $configs = $loader->import('*.yml');
        if (count($configs) > 1){
            $configs = array_reduce($configs, function($carry, $item) {
                if ($carry) {
                    $carry = array_merge($carry, $item);
                } else {
                    $carry = $item;
                }
                return $carry;
            });
        }
        $process    = new Processor();
        $definition = $this->definition;
        if (is_array($definition)) {
            $definition = current($definition);
        }
        $configs = $process->processModules($definition, $configs);
        if ($this->cacheable) {
            $this->setCache($configs);
        }
        if (is_string($context)) {
            return isset($configs[$context]) ? $configs[$context] : null;
        }
        if (is_array($context)) {
            $current = each($context);
            $config  = isset($configs[$current['key']]) ? $configs[$current['key']] : array();
            return isset($config[$current['value']]) ? $config[$current['value']] : null;
        }
    }


    /**
     * nsToPath
     *
     * @param string $namespace
     *
     * @return string
     */
    private function nsToPath(string $namespace): string
    {
        return DIRECTORY_SEPARATOR . \strtr($namespace, ['.' => '/', '-' => '/', '_' => '/']);
    }


    /**
     * getCache
     *
     * @param string|null $key
     *
     * @return mixed
     */
    public function getCache($key = null)
    {
        if (is_string($key)) {
            $item = $this->configCache->cacheAdapter()->getItem($key);
            return $item->get();
        }
        if (is_array($key)) {
            $current = each($key);
            $item    = $this->configCache->cacheAdapter()->getItem($current['key']);
            $config  = $item->get();
            return isset($config[$current['value']]) ? $config[$current['value']] : null;
        }
        $items   = $this->configCache->cacheAdapter()->getItems();
        $current = [];
        foreach ($items as $item) {
            $key           = $item->getKey();
            $current[$key] = $item->get();
        }
        return $current;
    }


    /**
     * setCache
     *
     * @param array $data data
     */
    protected function setCache(array $data)
    {
        $this->configCache->write($data);
    }

}
