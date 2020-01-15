<?php

/**
 * NodeBuilder.php
 *
 * PHP version 7
 * 
 * Licensed under the Apache License, Version 2.0 (the "License"),
 * see LICENSE for more details: http://www.apache.org/licenses/LICENSE-2.0.
 * 
 * @category PHP
 * @package  LOEYE
 * @author   Zhang Yi <loeyae@gmail.com>
 * @version  2020年1月15日 下午10:07:45
 * @link     https://github.com/loeyae/loeye.git
 */

namespace loeye\config;

/**
 * NodeBuilder
 *
 * @author   Zhang Yi <loeyae@gmail.com>
 */
class NodeBuilder extends \Symfony\Component\Config\Definition\Builder\NodeBuilder {

    public function __construct()
    {
        parent::__construct();
        $this->nodeMapping['array'] = ArrayNodeDefinition::class;
        $this->nodeMapping['regex'] = RegexNodeDefinition::class;
    }
    
    
    /**
     * Creates a child array node.
     *
     * @param string $name The name of the node
     *
     * @return RegexNodeDefinition The child node
     */
    public function regexNode($name)
    {
        return $this->node($name, 'regex');
    }
    
}
