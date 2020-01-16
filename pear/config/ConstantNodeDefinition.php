<?php

/**
 * ConstantNodeDefinition.php
 *
 * PHP version 7
 *
 * Licensed under the Apache License, Version 2.0 (the "License"),
 * see LICENSE for more details: http://www.apache.org/licenses/LICENSE-2.0.
 *
 * @category PHP
 * @package  LOEYE
 * @author   Zhang Yi <loeyae@gmail.com>
 * @version  2020年1月16日 下午4:49:54
 * @link     https://github.com/loeyae/loeye.git
 */

namespace loeye\config;

/**
 * ConstantNodeDefinition
 *
 * @author   Zhang Yi <loeyae@gmail.com>
 */
class ConstantNodeDefinition extends \Symfony\Component\Config\Definition\Builder\ScalarNodeDefinition
{
    /**
     * Instantiate a Node.
     *
     * @return ScalarNode The node
     */
    protected function instantiateNode()
    {
        return new ConstantNode($this->name, $this->parent, $this->pathSeparator);
    }
}
