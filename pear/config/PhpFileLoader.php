<?php

/**
 * PhpFileLoader.php
 *
 * PHP version 7
 * 
 * Licensed under the Apache License, Version 2.0 (the "License"),
 * see LICENSE for more details: http://www.apache.org/licenses/LICENSE-2.0.
 * 
 * @category PHP
 * @package  LOEYE
 * @author   Zhang Yi <loeyae@gmail.com>
 * @version  2020年1月8日 下午9:40:20
 * @link     https://github.com/loeyae/loeye.git
 */

namespace loeye\config;

use Symfony\Component\Config\Loader\FileLoader;

/**
 * PhpFileLoader
 *
 * @author   Zhang Yi <loeyae@gmail.com>
 */
class PhpFileLoader extends FileLoader {
    
    
    public function load($resource, $type = null) 
    {
        $path = $this->locator->locate($resource);
        $load = static function () use ($path) {
            return include $path;
        };
        return $load();
    }

    public function supports($resource, $type = null): bool 
    {
        return is_string($resource) && 'php' === pathinfo(
                        $resource,
                        PATHINFO_EXTENSION
        );
    }

}
