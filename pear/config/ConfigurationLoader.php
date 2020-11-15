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

use Psr\Cache\InvalidArgumentException;
use Symfony\Component\Cache\Exception\CacheException;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Exception\FileLoaderImportCircularReferenceException;
use Symfony\Component\Config\Exception\LoaderLoadException;

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
    protected $cacheAble;

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
     * __construct
     *
     * @param string $directory 配置文件基础目录
     * @param string $namespace 配置文件名称空间
     * @param array|ConfigurationInterface|null $definition 配置文件规则实例
     * @param boolean $cacheable 是否缓存
     * @param string $cacheDirectory 缓存目录
     * @throws CacheException
     */
    public function __construct($directory, $namespace, $definition = null, $cacheable = true, $cacheDirectory = null)
    {
        $this->directory = $directory;
        $this->namespace = $namespace;
        if (null === $definition) {
            $definition = new general\RulesetConfigDefinition();
        }
        $this->definition = $definition;
        $this->cacheAble  = $cacheable;
        if ($this->cacheAble) {
            if (null === $cacheDirectory) {
                $this->cacheDirectory = RUNTIME_CACHE_DIR . D_S .PROJECT_NAMESPACE .D_S . 'config';
                !defined('PROJECT_PROPERTY') ?: $this->cacheDirectory .= D_S . PROJECT_PROPERTY;
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
    public function getDirectory(): string
    {
        return $this->directory;
    }


    /**
     * load
     *
     * @param array|string|null $context
     *
     * @return array
     * @throws FileLoaderImportCircularReferenceException
     * @throws LoaderLoadException
     * @throws InvalidArgumentException
     */
    public function load($context = null): ?array
    {
        if (null === $context) {
            $context = Processor::DEFAULT_SETTINGS;
        }
        if ($this->cacheAble && !$this->configCache->isFresh()) {
            return $this->getCache($context);
        }
        $configs = $this->loadConfig();
        $process    = new Processor();
        $definition = $this->definition;
        if (!is_array($definition)) {
            $definition = [$definition];
        }
        $configs = $process->processConfigurations($definition, $configs);
        if ($this->cacheAble) {
            $this->setCache((array)$configs);
        }
        if (is_string($context)) {
            return $configs[$context] ?? null;
        }
        if (is_array($context)) {
            $current = ['key' => key($context), 'value' => current($context)];
            $config  = $configs[$current['key']] ?? array();
            if(empty($config)) {
                return null;
            }
            return $config[$current['value']] ?? null;
        }
        return null;
    }


    /**
     * loadModules
     *
     * @param array|string|null $context
     *
     * @return array
     * @throws FileLoaderImportCircularReferenceException
     * @throws LoaderLoadException
     * @throws InvalidArgumentException
     */
    public function loadModules($context = null): ?array
    {
        if (null === $context) {
            $context = Processor::DEFAULT_SETTINGS;
        }
        if ($this->cacheAble && !$this->configCache->isFresh()) {
            return $this->getCache($context);
        }
        $configs = $this->loadConfig();
        $process    = new Processor();
        $definition = $this->definition;
        if (is_array($definition)) {
            $definition = current($definition);
        }
        $configs = $process->processModules($definition, $configs);
        if ($this->cacheAble) {
            $this->setCache((array)$configs);
        }
        if (is_string($context)) {
            return $configs[$context] ?? null;
        }
        if (is_array($context)) {
            $current = ['key' => key($context), 'value' => current($context)];
            $config  = $configs[$current['key']] ?? array();
            return $config[$current['value']] ?? null;
        }
        return null;
    }

    /**
     * loadConfig
     *
     * @return array
     * @throws FileLoaderImportCircularReferenceException
     * @throws LoaderLoadException
     */
    protected function loadConfig(): array
    {
        $locator = new FileLocator($this->directory);
        $loader  = new YamlFileLoader($locator);
        $loader->setCurrentDir($this->nsToPath($this->namespace));
        $configs = $loader->import('*.yml');
        if (count($configs) > 1){
            $configs = array_reduce($configs, static function($carry, $item) {
                if ($carry) {
                    $carry = array_merge($carry, $item);
                } else {
                    $carry = $item;
                }
                return $carry;
            });
        }
        return $configs;
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
        return DIRECTORY_SEPARATOR . strtr($namespace, ['.' => '/', '-' => '/', '_' => '/']);
    }


    /**
     * getCache
     *
     * @param mixed $key
     *
     * @return mixed
     * @throws InvalidArgumentException
     */
    public function getCache($key = null)
    {
        if (is_string($key)) {
            $item = $this->configCache->cacheAdapter()->getItem($key);
            return $item->get();
        }
        if (is_array($key)) {
            $current = ['key' => key($key), 'value' => current($key)];
            $item    = $this->configCache->cacheAdapter()->getItem($current['key']);
            $config  = $item->get();
            return $config[$current['value']] ?? null;
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
     * @return void
     * @throws InvalidArgumentException
     */
    protected function setCache(array $data): void
    {
        $this->configCache->write($data);
    }

}
