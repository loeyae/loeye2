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
                     ->arrayNode('module')->canBeUnset()
                         ->children()
                            ->arrayNode('setting')
                                ->variablePrototype()->end()
                            ->end()
                            ->scalarNode('module_id')->isRequired()->end()
                            ->arrayNode('plugin')->isRequired()
                                ->arrayProtoType()
                                    ->children()
                                        ->scalarNode('name')->end()
                                        ->scalarNode('include_module')->end()
                                        ->scalarNode('in')->end()
                                        ->scalarNode('in_key')->end()
                                        ->scalarNode('out')->end()
                                        ->scalarNode('out_key')->end()
                                        ->scalarNode('err')->end()
                                        ->scalarNode('err_key')->end()
                                        ->regexNode('*')->end()
                                        ->arrayNode('parallel')
                                            ->arrayProtoType() 
                                                ->children()
                                                    ->scalarNode('name')->isRequired()->end()
                                                    ->scalarNode('include_module')->end()
                                                    ->scalarNode('in')->end()
                                                    ->scalarNode('in_key')->end()
                                                    ->scalarNode('out')->end()
                                                    ->scalarNode('out_key')->end()
                                                    ->scalarNode('err')->end()
                                                    ->scalarNode('err_key')->end()
                                                    ->regexNode('*')->end()
                                                    ->regexNode('#\w+#')
                                                        ->scalarPrototype()->end()
                                                    ->end()
                                                ->end()
                                            ->end()
                                        ->end()
                                        ->regexNode('#if \w+#')
                                            ->arrayProtoType() 
                                                ->children()
                                                    ->scalarNode('name')->isRequired()->end()
                                                    ->scalarNode('include_module')->end()
                                                    ->scalarNode('in')->end()
                                                    ->scalarNode('in_key')->end()
                                                    ->scalarNode('out')->end()
                                                    ->scalarNode('out_key')->end()
                                                    ->scalarNode('err')->end()
                                                    ->scalarNode('err_key')->end()
                                                    ->regexNode('*')->end()
                                                    ->arrayNode('parallel')
                                                        ->arrayProtoType() 
                                                            ->children()
                                                                ->scalarNode('name')->isRequired()->end()
                                                                ->scalarNode('include_module')->end()
                                                                ->scalarNode('in')->end()
                                                                ->scalarNode('in_key')->end()
                                                                ->scalarNode('out')->end()
                                                                ->scalarNode('out_key')->end()
                                                                ->scalarNode('err')->end()
                                                                ->scalarNode('err_key')->end()
                                                                ->regexNode('*')->end()
                                                                ->regexNode('#\w+#')
                                                                    ->scalarPrototype()->end()
                                                                ->end()
                                                            ->end()
                                                        ->end()
                                                    ->end()
                                                    ->regexNode('#\w+#')
                                                        ->scalarPrototype()->end()
                                                    ->end()
                                                ->end()
                                            ->end()
                                        ->end()
                                        ->regexNode('#\w+#')
                                            ->variablePrototype()->end()
                                        ->end()
                                    ->end()
                                ->end()
                            ->end()
                            ->arrayNode('view')->canBeUnset()
                                ->children()
                                    ->regexNode('#\w+#')
                                        ->children()
                                            ->scalarNode('src')->end()
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
