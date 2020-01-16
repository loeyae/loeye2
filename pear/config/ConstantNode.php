<?php

/**
 * ConstantNode.php
 *
 * PHP version 7
 *
 * Licensed under the Apache License, Version 2.0 (the "License"),
 * see LICENSE for more details: http://www.apache.org/licenses/LICENSE-2.0.
 *
 * @category PHP
 * @package  LOEYE
 * @author   Zhang Yi <loeyae@gmail.com>
 * @version  2020年1月16日 下午4:49:10
 * @link     https://github.com/loeyae/loeye.git
 */

namespace loeye\config;
use \Symfony\Component\Config\Definition\NodeInterface;

/**
 * ConstantNode
 *
 * @author   Zhang Yi <loeyae@gmail.com>
 */
class ConstantNode extends \Symfony\Component\Config\Definition\ScalarNode
{

    /**
     * @throws \InvalidArgumentException if the name contains a period
     */
    public function __construct(?string $name, NodeInterface $parent = null, string $pathSeparator = self::DEFAULT_PATH_SEPARATOR)
    {
        if (null != $name && false !== preg_match('/^[A-Z][A-Z0-9_]*$/', $name)) {
            throw new \InvalidArgumentException('The name expect match /^[A-Z][A-Z0-9_]*$/, actual: '. $name);
        }
        parent::__construct($name, $parent, $pathSeparator);
    }

    public function setName($name)
    {
        parent::setName(strtoupper($name));
    }

    /**
     * {@inheritdoc}
     */
    protected function mergeValues($leftSide, $rightSide)
    {
        return $leftSide;
    }
}
