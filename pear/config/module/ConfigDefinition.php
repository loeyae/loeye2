<?php

/**
 * ConfigDefinition.php
 *
 * PHP version 7
 *
 * Licensed under the Apache License, Version 2.0 (the "License"),
 * see LICENSE for more details: http://www.apache.org/licenses/LICENSE-2.0.
 *
 * @category PHP
 * @package  LOEYE
 * @author   Zhang Yi <loeyae@gmail.com>
 * @version  2020年1月8日 下午9:56:03
 * @link     https://github.com/loeyae/loeye.git
 */

namespace loeye\config\module;

use \Symfony\Component\Config\Definition\Builder\TreeBuilder;

/**
 * ConfigDefinition
 *
 * @author   Zhang Yi <loeyae@gmail.com>
 */
class ConfigDefinition implements \Symfony\Component\Config\Definition\ConfigurationInterface {


    /**
     * getConfigTreeBuilder
     *
     * @return TreeBuilder
     */
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder(0);
        $treeBuilder->getRootNode()
                 ->children()
                     ->arrayNode('settings')
                         ->children()
                             ->scalarNode(0)->isRequired()->end()
                         ->end()
                     ->end()
                     ->arrayPrototype()->canBeUnset()
                         ->children()
                            ->scalarNode('')->isRequired()->end()
                            ->integerNode(1)->isRequired()->end()
                            ->integerNode(2)->isRequired()->end()
                         ->end()
                     ->end()
                 ->end();
        return $treeBuilder;
    }


}
