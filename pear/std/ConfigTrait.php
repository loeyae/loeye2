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
     * @param string                            $property   property
     * @param string                            $bundle     bundle
     * @param array|ConfigurationInterface|null $definition definition
     *
     * @return \loeye\base\Configuration
     */
    protected function bundleConfig($property, $bundle = null, $definition = null)
    {
        $bundle = $property . ($bundle ? '/' . $bundle : '');
        $definition ?? $definition = (property_exists($this, 'definition') ? $this->definition : null);
        return new \loeye\base\Configuration(static::BUNDLE, $bundle, $definition);
    }

    /**
     * propertyConfig
     *
     * @param string $property property
     * @param string $bundle   bundle
     *
     * @return \loeye\base\Configuration
     */
    protected function propertyConfig($property, $bundle = null, ConfigurationInterface $definition = null)
    {
        $definition ?? ($definition = property_exists($this, 'definition') ? $this->definition : null);
        return new \loeye\base\Configuration($property, $bundle, $definition);
    }

}
