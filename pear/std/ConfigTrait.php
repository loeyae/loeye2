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
 * @version  GIT: $Id: Zhang Yi $
 * @link     https://github.com/loeyae/loeye.git
 */

namespace loeye\std;

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
     * @param string $bundle   bundle
     *
     * @return \loeye\base\Configuration
     */
    protected function bundleConfig($property, $bundle = null)
    {
        $bundle = $property . ($bundle ? '/' . $bundle : '');
        return new \loeye\base\Configuration(static::BUNDLE, $bundle);
    }

    /**
     * propertyConfig
     *
     * @param string $property property
     * @param string $bundle   bundle
     *
     * @return \loeye\base\Configuration
     */
    protected function propertyConfig($property, $bundle = null)
    {
        return new \loeye\base\Configuration($property, $bundle);
    }

}
