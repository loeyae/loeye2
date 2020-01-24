<?php

/**
 * DeltaDefinition.php
 *
 * PHP version 7
 *
 * Licensed under the Apache License, Version 2.0 (the "License"),
 * see LICENSE for more details: http://www.apache.org/licenses/LICENSE-2.0.
 *
 * @category PHP
 * @package  LOEYE
 * @author   Zhang Yi <loeyae@gmail.com>
 * @version  2020年1月19日 下午4:03:20
 * @link     https://github.com/loeyae/loeye.git
 */

namespace loeye\config\app;

use \loeye\config\TreeBuilder;

/**
 * DeltaDefinition
 *
 * @author   Zhang Yi <loeyae@gmail.com>
 */
class DeltaDefinition implements \Symfony\Component\Config\Definition\ConfigurationInterface
{
    //put your code here
    public function getConfigTreeBuilder(): \Symfony\Component\Config\Definition\Builder\TreeBuilder
    {
        $treeBuilder = new TreeBuilder('-');
        $treeBuilder->getRootNode()
                 ->children()
                     ->arrayNode('settings')
                         ->children()
                             ->arrayNode('0')->isRequired()
                                ->children()
                                    ->scalarNode('profile')->isRequired()->end()
                                ->end()
                             ->end()
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
                             ->arrayNode('database')
                                ->children()
                                    ->scalarNode('default')->end()
                                    ->booleanNode('is_dev_mode')->end()
                                    ->enumNode('encrypt_mode')->values([
                                        \loeye\base\ENCRYPT_MODE_EXPLICIT,
                                        \loeye\base\ENCRYPT_MODE_CRYPT,
                                        \loeye\base\ENCRYPT_MODE_KEYDB,
                                    ])->end()
                                    ->regexNode('*')->end()
                                ->end()
                             ->end()
                             ->regexNode('*')
                                 ->variablePrototype()->end()
                             ->end()
                         ->end()
                     ->end()
                     ->arrayNode('configuration')
                         ->children()
                             ->scalarNode('property_name')->isRequired()->cannotBeEmpty()->end()
                             ->scalarNode('timezone')->isRequired()->cannotBeEmpty()->end()
                             ->regexNode('*')
                                 ->variablePrototype()->end()
                             ->end()
                         ->end()
                     ->end()
                     ->arrayNode('locale')
                         ->children()
                             ->scalarNode('default')->end()
                             ->scalarNode('basename')->isRequired()->cannotBeEmpty()->end()
                             ->arrayNode('supported_languages')->canBeUnset()
                                 ->scalarPrototype()->end()
                             ->end()
                         ->end()
                     ->end()
                     ->regexNode('*')->end()
                     ->regexNode('#\w+#')
                         ->variablePrototype()->end()
                     ->end()
                 ->end();
        return $treeBuilder;
    }

}
