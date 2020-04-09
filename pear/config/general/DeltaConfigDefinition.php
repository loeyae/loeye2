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

namespace loeye\config\general;

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
                                ->scalarPrototype()->end()
                             ->end()
                         ->end()
                     ->end()
                     ->regexNode('*')
                        ->variablePrototype()->end()
                     ->end()
                 ->end();
        return $treeBuilder;
    }


}
