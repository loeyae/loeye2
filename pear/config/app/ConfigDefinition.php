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

namespace loeye\config\app;

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
                     ->arrayNode('constants')->canBeUnset()
                         ->constantPrototype()->end()
                     ->end()
                     ->arrayNode('application')->canBeUnset()
                         ->children()
                             ->enumNode('cache')->values([
                                 \loeye\base\Cache::CACHE_TYPE_APC,
                                 \loeye\base\Cache::CACHE_TYPE_ARRAY,
                                 \loeye\base\Cache::CACHE_TYPE_FILE,
                                 \loeye\base\Cache::CACHE_TYPE_MEMCACHED,
                                 \loeye\base\Cache::CACHE_TYPE_PHP_ARRAY,
                                 \loeye\base\Cache::CACHE_TYPE_PHP_FILE,
                                 \loeye\base\Cache::CACHE_TYPE_REDIS])->end()
                             ->variableNode('database')->cannotBeEmpty()->end()
                             ->arrayNode('setting')->canBeUnset()
                                 ->variablePrototype()->end()
                             ->end()
                         ->end()
                     ->end()
                     ->arrayNode('configuration')
                         ->children()
                             ->scalarNode('property_name')->isRequired()->cannotBeEmpty()->end()
                             ->scalarNode('timezone')->isRequired()->cannotBeEmpty()->end()
                             ->arrayNode('setting')->canBeUnset()
                                 ->variablePrototype()->end()
                             ->end()
                         ->end()
                     ->end()
                     ->arrayNode('local')
                         ->children()
                             ->scalarNode('basename')->isRequired()->cannotBeEmpty()->end()
                             ->arrayNode('supported_languages')->canBeUnset()
                                 ->scalarPrototype()->end()
                             ->end()
                         ->end()
                     ->end()
                 ->end();
        return $treeBuilder;
    }


}