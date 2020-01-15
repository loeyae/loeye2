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

namespace loeye\config\cache;

use \loeye\config\TreeBuilder;

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
        $treeBuilder = new TreeBuilder('-');
        $treeBuilder->getRootNode()
                 ->children()
                     ->arrayNode('settings')
                         ->children()
                             ->scalarNode(0)->isRequired()->defaultValue('master')->end()
                         ->end()
                     ->end()
                     ->arrayNode(\loeye\base\Cache::CACHE_TYPE_APC)->canBeUnset()
                         ->children()
                             ->integerNode('lifetime')->isRequired()->end()
                         ->end()
                     ->end()
                     ->arrayNode(\loeye\base\Cache::CACHE_TYPE_MEMCACHED)->canBeUnset()
                         ->children()
                             ->scalarNode('persistent_id')->cannotBeEmpty()->end()
                             ->integerNode('lifetime')->end()
                             ->arrayNode('servers')->isRequired()->cannotBeEmpty()
                                 ->arrayPrototype()
                                    ->children()
                                        ->scalarNode(0)->isRequired()->end()
                                        ->integerNode(1)->isRequired()->end()
                                        ->integerNode(2)->isRequired()->end()
                                    ->end()
                                 ->end()
                             ->end()
                         ->end()
                     ->end()
                     ->arrayNode(\loeye\base\Cache::CACHE_TYPE_REDIS)->canBeUnset()
                         ->children()
                             ->scalarNode('persistent')->cannotBeEmpty()->end()
                             ->scalarNode('host')->cannotBeEmpty()->end()
                             ->integerNode('port')->isRequired()->end()
                             ->scalarNode('password')->cannotBeEmpty()->end()
                             ->integerNode('timeout')->end()
                             ->integerNode('lifetime')->end()
                         ->end()
                     ->end()
                     ->arrayNode(\loeye\base\Cache::CACHE_TYPE_PHP_ARRAY)->canBeUnset()
                         ->children()
                             ->scalarNode('file')->cannotBeEmpty()->end()
                             ->integerNode('lifetime')->end()
                         ->end()
                     ->end()
                     ->arrayNode(\loeye\base\Cache::CACHE_TYPE_PHP_FILE)->canBeUnset()
                         ->children()
                             ->scalarNode('directory')->cannotBeEmpty()->end()
                             ->integerNode('lifetime')->end()
                         ->end()
                     ->end()
                     ->arrayNode(\loeye\base\Cache::CACHE_TYPE_FILE)->canBeUnset()
                         ->children()
                             ->scalarNode('directory')->cannotBeEmpty()->end()
                             ->integerNode('lifetime')->end()
                         ->end()
                     ->end()
                 ->end();
        return $treeBuilder;
    }


}
