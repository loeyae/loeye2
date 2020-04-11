<?php

/**
 * ConfigTrait.php
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

namespace loeye\std;

use loeye\base\AppConfig;
use loeye\base\Cache;
use loeye\base\Configuration;
use loeye\base\DB;
use loeye\config\cache\ConfigDefinition;
use \Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * ConfigTrait
 *
 * @author   Zhang Yi <loeyae@gmail.com>
 */
trait ConfigTrait
{

    /**
     * bundleConfig
     *
     * @param string $property property
     * @param string $bundle bundle
     * @param array|ConfigurationInterface|null $definition definition
     *
     * @return Configuration
     */
    protected function bundleConfig($property, $bundle = null, $definition = null): Configuration
    {
        $bundle = $property . ($bundle ? '/' . $bundle : '');
        $definition = property_exists($this, 'definition') ? $this->definition : $definition;
        return new Configuration(static::BUNDLE, $bundle, $definition);
    }

    /**
     * propertyConfig
     *
     * @param string $property property
     * @param string $bundle bundle
     * @param array|ConfigurationInterface|null $definition definition
     *
     * @return Configuration
     */
    protected function propertyConfig($property, $bundle = null, $definition = null): Configuration
    {
        $definition = property_exists($this, 'definition') ? $this->definition : $definition;
        return new Configuration($property, $bundle, $definition);
    }

    /**
     * cacheConfig
     *
     * @param AppConfig $appConfig
     * @return Configuration
     */
    protected function cacheConfig(AppConfig $appConfig): Configuration
    {
        $definition = new ConfigDefinition();
        return $this->propertyConfig($appConfig->getPropertyName(), Cache::BUNDLE, $definition);
    }

    /**
     * databaseConfig
     *
     * @param AppConfig $appConfig
     * @return Configuration
     */
    protected function databaseConfig(AppConfig $appConfig): Configuration
    {
        $definition = new \loeye\config\database\ConfigDefinition();
        return $this->propertyConfig($appConfig->getPropertyName(), DB::BUNDLE, $definition);
    }


}
