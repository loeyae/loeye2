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

use Symfony\Component\Config\Definition\Builder\ScalarNodeDefinition;

/**
 * ConstantNodeDefinition
 *
 * @author   Zhang Yi <loeyae@gmail.com>
 */
class ConstantNodeDefinition extends ScalarNodeDefinition
{
    /**
     * Instantiate a Node.
     *
     * @return ConstantNode The node
     */
    protected function instantiateNode(): ConstantNode
    {
        return new ConstantNode($this->name, $this->parent, $this->pathSeparator);
    }
}
