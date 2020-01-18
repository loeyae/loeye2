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
class Processor extends \Symfony\Component\Config\Definition\Processor {

    const DEFAULT_SETTINGS = 'master';
    const KEY_SETTINGS     = 'settings';
    const KEY_ARBITRARILY  = '*';


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
            $config        = $configTree->normalize($config);
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


    /**
     * processConfigurations
     * 
     * @param array $configurations
     * @param array $configs
     * 
     * @return type
     */
    public function processConfigurations(array $configurations, array $configs)
    {
        $currentConfig        = [];
        $currentConfiguration = [];
        foreach ($configs as $config) {
            $currentConfig = $this->processConfig($configurations, $config, $currentConfig, $currentConfiguration);
        }
        $current = [];
        foreach ($currentConfig as $key => $value) {
            if (static::DEFAULT_SETTINGS === $key) {
                $tree                              = $currentConfiguration[$key];
                $finalize                          = $tree->finalize($value);
                unset($finalize[static::KEY_SETTINGS]);
                unset($finalize[static::KEY_ARBITRARILY]);
                $current[static::DEFAULT_SETTINGS] = $finalize;
            } else {
                foreach ($value as $k => $v) {
                    $tree     = $currentConfiguration[$key][$k];
                    $finalize = $tree->finalize($v);
                    unset($finalize[static::KEY_SETTINGS]);
                    unset($finalize[static::KEY_ARBITRARILY]);
                    if (!isset($current[$key])) {
                        $current[$key] = [$k => $finalize];
                    } else {
                        $current[$key][$k] = $finalize;
                    }
                }
            }
        }
        return $current;
    }


    protected function processConfig($configurations, $config, $currentConfig, &$currentConfiguration)
    {
        $ex = null;
        foreach ($configurations as $configuration) {
            try {
                if ($configuration instanceof ConfigurationInterface) {
                    $tree    = $configuration->getConfigTreeBuilder()->buildTree();
                    $config  = $tree->normalize($config);
                    $config  = $this->parseConfig($config, $tree, $currentConfiguration);
                    $setting = each($config);
                    if (static::DEFAULT_SETTINGS === $setting['key']) {
                        if (!isset($currentConfig[static::DEFAULT_SETTINGS])) {
                            $currentConfig[static::DEFAULT_SETTINGS] = [];
                        }
                        $currentConfig[static::DEFAULT_SETTINGS] = $tree->merge($currentConfig[static::DEFAULT_SETTINGS], $setting['value']);
                    } else {
                        if (!isset($currentConfig[$setting['key']])) {
                            $currentConfig[$setting['key']] = [];
                        }
                        $delta = each($setting['value']);
                        if (!isset($currentConfig[$setting['key']][$delta['key']])) {
                            $currentConfig[$setting['key']][$delta['key']] = [];
                        }
                        $currentConfig[$setting['key']][$delta['key']] = $tree->merge($currentConfig[$setting['key']][$delta['key']], $delta['value']);
                    }
                    $ex = null;
                    break;
                }
            } catch (\Exception $exc) {
                $ex = $exc;
            }
        }
        if (null !== $ex) {
            throw $ex;
        }
        return $currentConfig;
    }


    /**
     * parseConfig
     * 
     * @param array $config
     * @param type $tree
     * @param type $currentConfiguration
     * 
     * @return type
     * @throws type
     */
    protected function parseConfig($config, $tree, &$currentConfiguration)
    {
        $settings = $config[static::KEY_SETTINGS];
        if (static::DEFAULT_SETTINGS === $settings[0]) {
            $currentConfiguration[static::DEFAULT_SETTINGS] = $tree;
            return [static::DEFAULT_SETTINGS => $config];
        } else {
            if (is_array($settings[0])) {
                $setting = each($settings[0]);
                if (!isset($currentConfiguration[$setting['key']])) {
                    $currentConfiguration[$setting['key']] = [$setting['value'] => $tree];
                } else {
                    $currentConfiguration[$setting['key']][$setting['value']] = $tree;
                }
                return [$setting['key'] => [$setting['value'] => $config]];
            }
            throw \InvalidArgumentException('settings is invalide');
        }
    }

}
