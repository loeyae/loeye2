<?php

/**
 * TreeBuilder.php
 *
 * PHP version 7
 * 
 * Licensed under the Apache License, Version 2.0 (the "License"),
 * see LICENSE for more details: http://www.apache.org/licenses/LICENSE-2.0.
 * 
 * @category PHP
 * @package  LOEYE
 * @author   Zhang Yi <loeyae@gmail.com>
 * @version  2020年1月15日 下午10:17:05
 * @link     https://github.com/loeyae/loeye.git
 */

namespace loeye\config;
use \Symfony\Component\Config\Definition\Builder\NodeBuilder as BaseNodeBuilder;
/**
 * TreeBuilder
 *
 * @author   Zhang Yi <loeyae@gmail.com>
 */
class TreeBuilder extends \Symfony\Component\Config\Definition\Builder\TreeBuilder {

    public function __construct(string $name = null, string $type = 'array', BaseNodeBuilder $builder = null)
    {
        if (null === $name) {
            @trigger_error('A tree builder without a root node is deprecated since Symfony 4.2 and will not be supported anymore in 5.0.', E_USER_DEPRECATED);
        } else {
            $builder = $builder ?: new NodeBuilder();
            $this->root = $builder->node($name, $type)->setParent($this);
        }
    }

    /**
     * Creates the root node.
     *
     * @param string $name The name of the root node
     * @param string $type The type of the root node
     *
     * @return ArrayNodeDefinition|NodeDefinition The root node (as an ArrayNodeDefinition when the type is 'array')
     *
     * @throws \RuntimeException When the node type is not supported
     *
     * @deprecated since Symfony 4.3, pass the root name to the constructor instead
     */
    public function root($name, $type = 'array', BaseNodeBuilder $builder = null)
    {
        @trigger_error(sprintf('The "%s()" method called for the "%s" configuration is deprecated since Symfony 4.3, pass the root name to the constructor instead.', __METHOD__, $name), E_USER_DEPRECATED);

        $builder = $builder ?: new NodeBuilder();

        return $this->root = $builder->node($name, $type)->setParent($this);
    }
}
