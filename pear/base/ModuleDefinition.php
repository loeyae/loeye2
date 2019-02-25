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
 * @version  GIT: $Id: Zhang Yi $
 * @link     https://github.com/loeyae/loeye.git
 */

namespace loeye\base;

/**
 * Description of ModuleDefinition
 *
 * @author   Zhang Yi <loeyae@gmail.com>
 */
class ModuleDefinition
{

    use \loeye\std\ConfigTrait;

    const PLUGIN_REMOVE_FROM_MODULE = 'lyRemovePlugin';
    const BUNDLE = 'modules';

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
        'src'  => null,
    );

    /**
     * __construct
     *
     * @param \loeye\base\AppConfig $appConfig AppConfig instance
     * @param string                $moduleId  module id
     *
     * @return void
     * @throws Exception
     */
    public function __construct(AppConfig $appConfig, $moduleId)
    {
        $this->appConfig = $appConfig;
        $explode         = explode('.', $moduleId);
        $bundle          = isset($explode[2]) ? $explode[1] : null;
        $this->config    = $this->bundleConfig($appConfig->getPropertyName(), $bundle);
        $this->_initModule($moduleId);
        $this->_parseModuleDefinition();
    }

    /**
     * getModuleId
     *
     * @return string
     */
    public function getModuleId()
    {
        return $this->_moduleId;
    }

    /**
     * getInputs
     *
     * @return array
     */
    public function getInputs()
    {
        return $this->_inputs;
    }

    /**
     * getSetting
     *
     * @return array
     */
    public function getSetting()
    {
        return $this->_setting;
    }

    /**
     * getPlugins
     *
     * @return array
     */
    public function getPlugins()
    {
        return $this->_plugins;
    }

    /**
     * getMockPlugins
     *
     * @return array
     */
    public function getMockPlugins()
    {
        return $this->_mockPlugins;
    }

    /**
     * getViews
     *
     * @return array
     */
    public function getViews()
    {
        return $this->_views;
    }

    /**
     * getView
     *
     * @param string $renderId render id
     *
     * @return array
     * @throws Exception
     */
    public function getView($renderId = 'default')
    {
        if ($renderId === null) {
            return array();
        }
        if (!isset($this->_views[$renderId])) {
            throw new Exception(
                    'render:' . $renderId . '不存在',
                    Exception::INVALID_PARAMETER_CODE
            );
        }
        return $this->_views[$renderId];
    }

    /**
     * parseModuleDefinition
     *
     * @return void
     */
    private function _parseModuleDefinition()
    {
        $this->_moduleId = $this->_currentModule['module_id'];
        $inputs          = array();
        if (!empty($this->_currentModule['inputs'])) {
            if (!is_array($this->_currentModule['inputs'])) {
                throw new Exception(
                        "无效的module设置：inputs",
                        Exception::INVALID_CONFIG_SET_CODE
                );
            }
            $inputs = $this->_currentModule['inputs'];
        }
        $this->_inputs = $inputs;
        $setting       = array();
        if (!empty($this->_currentModule['setting'])) {
            if (!is_array($this->_currentModule['setting'])) {
                throw new Exception(
                        "无效的module设置：setting",
                        Exception::INVALID_CONFIG_SET_CODE
                );
            }
            $setting = $this->_currentModule['setting'];
        }
        $this->_setting = $setting;
        $mockPlugins    = array();
        if (!empty($this->_currentModule['mock_plugin'])) {
            $mockPlugins        = $this->_currentModule['mock_plugin'];
            $this->_parsePlugin($mockPlugins);
            $this->_mockPlugins = $this->_plugins;
            $this->_plugins     = array();
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
    private function _parsePlugin($plugins)
    {
        $this->_plugins = array();
        $i              = 0;
        foreach ($plugins as $plugin) {
            if (isset($plugin['include_module'])) {
                $includePlugins = null;
                if (array_key_exists($plugin['include_module'], $this->_modules)) {
                    $includePlugins = $this->_modules[$plugin['include_module']]['plugin'];
                } else {
                    $includeModuleDfn = new self($this->appConfig, $plugin['include_module']);
                    $includePlugins   = $includeModuleDfn->getPlugins();
                }
                if ($includePlugins) {
                    $includeSettings = isset($plugin['setting']) ? (array) $plugin['setting'] : array();
                    foreach ($includePlugins as $includePlugin) {
                        if (isset($includePlugin['name']) && isset($includeSettings[$includePlugin['name']])) {
                            $includeSetting = $includeSettings[$includePlugin['name']];
                            if (isset($includeSetting[self::PLUGIN_REMOVE_FROM_MODULE]) &&
                                    $includeSetting[self::PLUGIN_REMOVE_FROM_MODULE] == true) {
                                unset($includeSettings[$includePlugin['name']]);
                                continue;
                            }
                            $settings      = array_diff_key($includeSetting, $this->forbiddenKey);
                            $includePlugin = array_merge($includePlugin, $settings);
                            unset($includeSettings[$includePlugin['name']]);
                        }
                        $this->_plugins[$i] = $includePlugin;
                        $i++;
                    }
                } else {
                    throw new Exception(
                            'include module:' . $plugin['include_module'] . '不存在',
                            Exception::MODULE_NOT_FOUND_CODE
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
     * @return void
     */
    private function _initModule($moduleId)
    {
        $config = $this->config->getConfig();
        foreach ($config as $moduleSetting) {
            if (!empty($moduleSetting['module']) && !empty($moduleSetting['module']['module_id'])
            ) {
                $this->_modules[$moduleSetting['module']['module_id']] = $moduleSetting['module'];
                if ($moduleId == $moduleSetting['module']['module_id']) {
                    $this->_currentModule = $moduleSetting['module'];
                    return true;
                }
            }
        }
        throw new Exception("Not Found", Exception::MODULE_NOT_FOUND_CODE);
    }

    /**
     * init
     *
     * @param array $moduleSetting module setting
     *
     * @return void
     */
    protected function init($moduleSetting)
    {
        $this->_moduleId    = $moduleSetting['id'];
        $this->_inputs      = $moduleSetting['inputs'];
        $this->_setting     = $moduleSetting['setting'];
        $this->_mockPlugins = $moduleSetting['mockps'];
        $this->_plugins     = $moduleSetting['plugins'];
        $this->_views       = $moduleSetting['views'];
    }

}
