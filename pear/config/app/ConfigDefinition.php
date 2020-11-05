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

use loeye\base\Cache;
use loeye\config\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use const loeye\base\ENCRYPT_MODE_CRYPT;
use const loeye\base\ENCRYPT_MODE_EXPLICIT;
use const loeye\base\ENCRYPT_MODE_KEYDB;

/**
 * ConfigDefinition
 *
 * @author   Zhang Yi <loeyae@gmail.com>
 */
class ConfigDefinition implements ConfigurationInterface {


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
                     ->scalarNode('profile')->end()
                     ->scalarNode('debug')->end()
                     ->arrayNode('constants')->canBeUnset()
                         ->constantPrototype()->end()
                     ->end()
                     ->arrayNode('application')->canBeUnset()
                         ->children()
                             ->enumNode('cache')->values([
                                 Cache::CACHE_TYPE_APC,
                                 Cache::CACHE_TYPE_ARRAY,
                                 Cache::CACHE_TYPE_FILE,
                                 Cache::CACHE_TYPE_MEMCACHED,
                                 Cache::CACHE_TYPE_PHP_ARRAY,
                                 Cache::CACHE_TYPE_PHP_FILE,
                                 Cache::CACHE_TYPE_REDIS])->end()
                             ->arrayNode('database')
                                ->children()
                                    ->scalarNode('default')->end()
                                    ->booleanNode('is_dev_mode')->end()
                                    ->enumNode('encrypt_mode')->values([
                                        ENCRYPT_MODE_EXPLICIT,
                                        ENCRYPT_MODE_CRYPT,
                                        ENCRYPT_MODE_KEYDB,
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
