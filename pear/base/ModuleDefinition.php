<?php

/**
 * ModuleDefinition.php
 *
 * PHP version 7
 *
 * Licensed under the Apache License, Version 2.0 (the "License"),
 * see LICENSE for more details: http://www.apache.org/licenses/LICENSE-2.0.
 *
 * @category PHP
 * @package  LOEYE
 * @author   Zhang Yi <loeyae@gmail.com>
 * @version  2018-07-23 22:44:28
 * @link     https://github.com/loeyae/loeye2.git
 */

namespace loeye\base;

use loeye\config\module\ConfigDefinition;
use loeye\std\ConfigTrait;
use loeye\error\{ResourceException, BusinessException};

/**
 * Description of ModuleDefinition
 *
 * @author   Zhang Yi <loeyae@gmail.com>
 */
class ModuleDefinition
{

    use ConfigTrait;

    public const PLUGIN_REMOVE_FROM_MODULE = 'lyRemovePlugin';
    public const BUNDLE = 'modules';

    private $_modules;
    private $_currentModule;
    private $_plugins;
    private $_mockPlugins;
    private $_views;
    private $_inputs;
    private $_setting;
    protected $_moduleId;
    protected $appConfig;
    protected $config;
    protected $forbiddenKey = array(
        'name' => null,
        'src' => null,
    );

    /**
     * __construct
     *
     * @param AppConfig $appConfig AppConfig instance
     * @param string $moduleId module id
     *
     * @return void
     * @throws Exception
     */
    public function __construct(AppConfig $appConfig, $moduleId)
    {
        $this->appConfig = $appConfig;
        $explode = explode('.', $moduleId);
        $bundle = null;
        if (count($explode) > 2) {
            array_shift($explode);
            array_pop($explode);
            $bundle = implode('/', $explode);
        }
        $definition = new ConfigDefinition();
        $this->config = $this->bundleConfig($appConfig->getPropertyName(), $bundle, $definition);
        $this->_initModule($moduleId);
        $this->_parseModuleDefinition();
    }

    /**
     * getModuleId
     *
     * @return string|null
     */
    public function getModuleId(): ?string
    {
        return $this->_moduleId;
    }

    /**
     * getInputs
     *
     * @return array|null
     */
    public function getInputs(): ?array
    {
        return $this->_inputs;
    }

    /**
     * getSetting
     *
     * @return array|null
     */
    public function getSetting(): ?array
    {
        return $this->_setting;
    }

    /**
     * getPlugins
     *
     * @return array|null
     */
    public function getPlugins(): ?array
    {
        return $this->_plugins;
    }

    /**
     * getMockPlugins
     *
     * @return array|null
     */
    public function getMockPlugins(): ?array
    {
        return $this->_mockPlugins;
    }

    /**
     * getViews
     *
     * @return array|null
     */
    public function getViews(): ?array
    {
        return $this->_views;
    }

    /**
     * getView
     *
     * @param string $renderId render id
     *
     * @return array|null
     * @throws Exception
     */
    public function getView($renderId = 'default'): ?array
    {
        if ($renderId === null) {
            return array();
        }
        if (!isset($this->_views[$renderId])) {
            throw new BusinessException(
                BusinessException::INVALID_RENDER_SET_MSG,
                BusinessException::INVALID_RENDER_SET_CODE
            );
        }
        return $this->_views[$renderId];
    }

    /**
     * parseModuleDefinition
     *
     * @return void
     * @throws BusinessException
     * @throws Exception
     */
    private function _parseModuleDefinition(): void
    {
        $this->_moduleId = $this->_currentModule['module_id'];
        $inputs = array();
        if (!empty($this->_currentModule['inputs'])) {
            if (!is_array($this->_currentModule['inputs'])) {
                throw new BusinessException(
                    BusinessException::INVALID_MODULE_SET_MSG,
                    BusinessException::INVALID_MODULE_SET_CODE,
                    ['mode' => 'inputs']
                );
            }
            $inputs = $this->_currentModule['inputs'];
        }
        $this->_inputs = $inputs;
        $setting = array();
        if (!empty($this->_currentModule['setting'])) {
            if (!is_array($this->_currentModule['setting'])) {
                throw new BusinessException(
                    BusinessException::INVALID_MODULE_SET_MSG,
                    BusinessException::INVALID_MODULE_SET_CODE,
                    ['mode' => 'setting']
                );
            }
            $setting = $this->_currentModule['setting'];
        }
        $this->_setting = $setting;
        if (!empty($this->_currentModule['mock_plugin'])) {
            $mockPlugins = $this->_currentModule['mock_plugin'];
            $this->_parsePlugin($mockPlugins);
            $this->_mockPlugins = $this->_plugins;
            $this->_plugins = array();
        }
        $plugins = array();
        if (!empty($this->_currentModule['plugin'])) {
            $plugins = $this->_currentModule['plugin'];
        }
        $this->_parsePlugin($plugins);

        $views = array();
        if (!empty($this->_currentModule['view'])) {
            $views = $this->_currentModule['view'];
        }
        $this->_views = $views;
    }

    /**
     * _parsePlugin
     *
     * @param array $plugins plugins
     *
     * @return void
     * @throws Exception
     */
    private function _parsePlugin($plugins): void
    {
        $this->_plugins = array();
        $i = 0;
        foreach ($plugins as $plugin) {
            if (isset($plugin['include_module'])) {
                $includePlugins = null;
                if (array_key_exists($plugin['include_module'], $this->_modules)) {
                    $includePlugins = $this->_modules[$plugin['include_module']]['plugin'];
                } else {
                    $includeModuleDfn = new self($this->appConfig, $plugin['include_module']);
                    $includePlugins = $includeModuleDfn->getPlugins();
                }
                if ($includePlugins) {
                    $includeSettings = isset($plugin['setting']) ? (array)$plugin['setting'] : array();
                    foreach ($includePlugins as $includePlugin) {
                        $plugin = $includePlugin;
                        if (isset($includePlugin['name'], $includeSettings[$includePlugin['name']])) {
                            $includeSetting = $includeSettings[$includePlugin['name']];
                            if (isset($includeSetting[self::PLUGIN_REMOVE_FROM_MODULE]) &&
                                $includeSetting[self::PLUGIN_REMOVE_FROM_MODULE] === true) {
                                unset($includeSettings[$includePlugin['name']]);
                                continue;
                            }
                            $settings = array_diff_key($includeSetting, $this->forbiddenKey);
                            $plugin = array_merge($includePlugin, $settings);
                            unset($includeSettings[$includePlugin['name']]);
                        }
                        $this->_plugins[$i] = $plugin;
                        $i++;
                    }
                } else {
                    throw new ResourceException(
                        ResourceException::MODULE_NOT_EXISTS_MSG,
                        ResourceException::MODULE_NOT_EXISTS_CODE,
                        ['module' => $plugin['include_module']]
                    );
                }
            } else {
                $this->_plugins[$i] = $plugin;
                $i++;
            }
        }
    }

    /**
     * _initModule
     *
     * @param string $moduleId module id
     *
     * @return bool
     * @throws Exception
     */
    private function _initModule($moduleId): bool
    {
        $config = $this->config->getConfig();
        foreach ($config as $moduleSetting) {
            if (!empty($moduleSetting['module']) && !empty($moduleSetting['module']['module_id'])
            ) {
                $this->_modules[$moduleSetting['module']['module_id']] = $moduleSetting['module'];
                if ($moduleId === $moduleSetting['module']['module_id']) {
                    $this->_currentModule = $moduleSetting['module'];
                    return true;
                }
            }
        }
        throw new ResourceException(ResourceException::MODULE_NOT_EXISTS_MSG, ResourceException::MODULE_NOT_EXISTS_CODE, ['module' => $moduleId]);
    }

    /**
     * init
     *
     * @param array $moduleSetting module setting
     *
     * @return void
     */
    protected function init($moduleSetting): void
    {
        $this->_moduleId = $moduleSetting['id'];
        $this->_inputs = $moduleSetting['inputs'];
        $this->_setting = $moduleSetting['setting'];
        $this->_mockPlugins = $moduleSetting['mockps'];
        $this->_plugins = $moduleSetting['plugins'];
        $this->_views = $moduleSetting['views'];
    }

}
