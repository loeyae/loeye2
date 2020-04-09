<?php

/**
 * RulesetConfigDefinition.php
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
 * RulesetConfigDefinition
 *
 * @author   Zhang Yi <loeyae@gmail.com>
 */
class RulesetConfigDefinition implements ConfigurationInterface {


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
                             ->scalarNode('0')->isRequired()->defaultValue('master')->end()
                         ->end()
                     ->end()
                     ->arrayNode('rulesets')->isRequired()
                        ->regexPrototype('#\w+#')->isRequired()
                            ->children()
                                ->scalarNode('type')->isRequired()->end()
                                ->integerNode('min')->end()
                                ->integerNode('max')->end()
                                ->integerNode('min_length')->end()
                                ->integerNode('max_length')->end()
                                ->arrayNode('length')
                                    ->integerPrototype()->end()
                                ->end()
                                ->integerNode('min_count')->end()
                                ->integerNode('max_count')->end()
                                ->arrayNode('count')
                                    ->integerPrototype()->end()
                                ->end()
                                ->arrayNode('callback')
                                    ->arrayPrototype()
                                        ->children()
                                            ->variableNode('name')->isRequired()->end()
                                            ->scalarNode('message')->end()
                                        ->end()
                                    ->end()
                                ->end()
                                ->arrayNode('fun')
                                    ->arrayPrototype()
                                        ->children()
                                            ->scalarNode('name')->isRequired()->end()
                                            ->arrayNode('params')
                                                ->variablePrototype()->end()
                                            ->end()
                                        ->end()
                                    ->end()
                                ->end()
                                ->arrayNode('filter')
                                    ->children()
                                        ->scalarNode('filter_type')->isRequired()->end()
                                        ->scalarNode('filter_flag')->end()
                                        ->scalarNode('filter_options')->end()
                                        ->scalarNode('options')->end()
                                    ->end()
                                ->end()
                                ->arrayNode('regex')
                                    ->children()
                                        ->scalarNode('pattern')->isRequired()->end()
                                    ->end()
                                ->end()
                                ->regexNode('#(\w+)_errmsg#')->end()
                            ->end()
                        ->end()
                     ->end()
                 ->end();
        return $treeBuilder;
    }


}
