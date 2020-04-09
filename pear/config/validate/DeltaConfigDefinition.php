<?php

/**
 * DeltaConfigDefinition.php
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

namespace loeye\config\validate;

use \loeye\config\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * DeltaConfigDefinition
 *
 * @author   Zhang Yi <loeyae@gmail.com>
 */
class DeltaConfigDefinition implements ConfigurationInterface {


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
                             ->arrayNode('0')->isRequired()
                                ->children()
                                    ->scalarNode('validate')->isRequired()->end()
                                ->end()
                             ->end()
                         ->end()
                     ->end()
                     ->arrayNode('validate')->isRequired()
                        ->children()
                            ->arrayNode('fields')->isRequired()
                                ->children()
                                    ->regexNode('#\w+#')
                                        ->children()
                                            ->variableNode('rule')->isRequired()->end()
                                            ->booleanNode('required_value')->end()
                                            ->arrayNode('required_value_if_match')
                                                ->variablePrototype()->end()
                                            ->end()
                                            ->arrayNode('required_value_if_include')
                                                ->variablePrototype()->end()
                                            ->end()
                                            ->arrayNode('required_value_if_key_exists')
                                                ->variablePrototype()->end()
                                            ->end()
                                            ->booleanNode('required_value_if_blank')->end()
                                            ->arrayNode('item')
                                                ->variablePrototype()->end()
                                            ->end()
                                        ->end()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                     ->end()
                 ->end();
        return $treeBuilder;
    }


}
