<?php

/**
 * Processor.php
 *
 * PHP version 7
 *
 * Licensed under the Apache License, Version 2.0 (the "License"),
 * see LICENSE for more details: http://www.apache.org/licenses/LICENSE-2.0.
 *
 * @category PHP
 * @package  LOEYE
 * @author   Zhang Yi <loeyae@gmail.com>
 * @version  2020年1月16日 下午7:25:18
 * @link     https://github.com/loeyae/loeye.git
 */

namespace loeye\config;

use \Symfony\Component\Config\Definition\ConfigurationInterface;
use \Symfony\Component\Config\Definition\NodeInterface;

/**
 * Processor
 *
 * @author   Zhang Yi <loeyae@gmail.com>
 */
class Processor extends \Symfony\Component\Config\Definition\Processor
{
        /**
     * Processes an array of configurations.
     *
     * @param array $configs An array of configuration items to process
     *
     * @return array The processed configuration
     */
    public function process(NodeInterface $configTree, array $configs)
    {
        $currentConfig = [];
        foreach ($configs as $config) {
            $config = $configTree->normalize($config);
            $currentConfig = $configTree->merge($currentConfig, $config);
        }

        return $configTree->finalize($currentConfig);
    }
    
    
    /**
     * Processes an array of configurations.
     *
     * @param array $configs An array of configuration items to process
     *
     * @return array The processed configuration
     */
    public function processConfiguration(ConfigurationInterface $configuration, array $configs)
    {
        return $this->process($configuration->getConfigTreeBuilder()->buildTree(), $configs);
    }
}
