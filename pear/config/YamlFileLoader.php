<?php

/**
 * YamlFileLoader.php
 *
 * PHP version 7
 *
 * Licensed under the Apache License, Version 2.0 (the "License"),
 * see LICENSE for more details: http://www.apache.org/licenses/LICENSE-2.0.
 *
 * @category PHP
 * @package  LOEYE
 * @author   Zhang Yi <loeyae@gmail.com>
 * @version  2020年1月7日 下午5:28:24
 * @link     https://github.com/loeyae/loeye.git
 */

namespace loeye\config;

use Symfony\Component\Yaml\Yaml;

/**
 * YamlFileLoader
 *
 * @author   Zhang Yi <loeyae@gmail.com>
 */
class YamlFileLoader extends \Symfony\Component\Config\Loader\FileLoader
{

    private $parser = null;

    public function load($resource, $type = null)
    {
        if ($this->parser == null) {
            $this->parser = new \Symfony\Component\Yaml\Parser();
        }
        $path = $this->locator->locate($resource);
        return $this->parser->parseFile($path, Yaml::PARSE_CONSTANT | Yaml::PARSE_CUSTOM_TAGS);
    }

    public function supports($resource, $type = null): bool
    {
        return is_string($resource) && in_array(pathinfo(
                        $resource,
                        PATHINFO_EXTENSION
        ), ['yml', 'yaml']);
    }

}
