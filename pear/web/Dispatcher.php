<?php

/**
 * Dispatcher.php
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

namespace loeye\web;

define('LOEYE_PLUGIN_HAS_ERROR', 'lyHasError');

/**
 * Description of Dispatcher
 *
 * @author   Zhang Yi <loeyae@gmail.com>
 */
class Dispatcher extends \loeye\std\Dispatcher
{

    private $_parse;
    private $_mDfnObj;

    /**
     * dispatche
     *
     * @param mixed $moduleId module id
     *
     * @return void
     */
    public function dispatche($moduleId = null)
    {
        try {
            $moduleId       = $this->parseUrl($moduleId);
            $this->initIOObject($moduleId);
            $this->initAppConfig();
            $this->initConfigConstants();
            $this->setTimezone();
            $this->initComponent();
            $this->_mDfnObj = new \loeye\base\ModuleDefinition($this->context->getAppConfig(), $moduleId);

            $this->excuteModule($moduleId);

            $this->redirectUrl();
            $view = $this->getView();
            $this->excuteView($view);
            $this->excuteOutput();
        } catch (\loeye\base\Exception $exc) {
            \loeye\base\ExceptionHandler($exc, $this->context);
        } catch (\Exception $exc) {
            \loeye\base\ExceptionHandler($exc, $this->context);
        }
        if ($this->proccessMode == LOEYE_PROCESS_MODE__TEST) {
            $this->_setTraceDataIntoContext(array());
            \loeye\base\Utils::logContextTrace($this->context);
        }
    }

    /**
     * setUrlManager
     *
     * @param array $setting rewrite rule setting
     * <p>
     * $setting = ['/<property_name:\w+>/<module:\w+>/<id:\w>/' => '{property_name}.{module}.{id}']
     * </p>
     */
    public function setUrlManager(array $setting)
    {
        $router = new \loeye\base\UrlManager($setting);
        $this->context->setUrlManager($router);
    }

    /**
     * excute_module
     *
     * @param string $moduleId module id
     *
     * @return void
     */
    protected function excuteModule($moduleId)
    {
        if (empty($moduleId)) {
            $moduleId = $this->context->getRequest()->getModuleId();
        };
        $this->context->setModule($this->_mDfnObj);

        $clientManager = new \loeye\client\ParallelClientManager();
        $this->context->setParallelClientManager($clientManager);

        $inputs          = $this->_mDfnObj->getInputs();
        $this->_setArrayInContext($inputs);
        $setting         = \loeye\lib\ModuleParse::parseInput($this->_mDfnObj->getSetting(), $this->context);
        $continueOnError = false;
        if (isset($setting['continue_on_error']) && $setting['continue_on_error'] === 'true') {
            $continueOnError = true;
        }
        $cacheAble = true;
        if (isset($setting['cache_able'])) {
            $cacheAble = \loeye\lib\ModuleParse::conditionResult($setting['cache_able'], $this->context);
        }
        if ($cacheAble && isset($setting['cache'])) {
            $this->context->setExpire($setting['cache']);
        }
        if ($cacheAble) {
            $this->context->loadCacheData();
        }

        if ($this->proccessMode == LOEYE_PROCESS_MODE__TEST) {
            $this->_setTraceDataIntoContext(array());
        }
        $mockMode = $this->context->getRequest()->getParameterGet('ly_p_m');
        if ($this->proccessMode == LOEYE_PROCESS_MODE__TEST && $mockMode === 'mock') {
            $mockPlugins = $this->_mDfnObj->getMockPlugins();
            list($returnStatus) = $this->_excutePlugin($mockPlugins, false, true);
        } else {
            $plugins = $this->_mDfnObj->getPlugins();
            list($returnStatus) = $this->_excutePlugin($plugins, false, $continueOnError);
        }
        if ($cacheAble) {
            $this->context->cacheData();
        }
        if (!empty($returnStatus) && is_string($returnStatus)) {
            $this->context->getResponse()->setRenderId($returnStatus);
        }
    }

    /**
     * initIOObject
     *
     * @param string $moduleId moduleId
     *
     * @return void
     */
    protected function initIOObject($moduleId)
    {
        $request = new \loeye\web\Request($moduleId);

        $this->context->setRequest($request);

        $response = new \loeye\web\Response();
        if (defined('MOBILE_RENDER_ENABLE') && MOBILE_RENDER_ENABLE) {
            if ($request->device) {
                $response->setRenderId(\loeye\web\Response::DEFAULT_MOBILE_RENDER_ID);
            }
        }
        $this->context->setResponse($response);
    }

    /**
     * _excuteRouter
     *
     * @param string $routerDir router dir
     *
     * @return string|null
     */
    private function _excuteRouter($routerDir)
    {
        $moduleId = null;

        if ($routerDir) {
            $router     = new \loeye\base\Router($routerDir);
            $this->context->setRouter($router);
            $requestUrl = filter_input(INPUT_SERVER, 'REQUEST_URI');
            $moduleId   = $router->match($requestUrl);
        }
        !$moduleId ?: $_GET['m_id'] = $moduleId;
        return $moduleId;
    }

    /**
     * getView
     *
     * @return array|null
     */
    protected function getView()
    {
        $renderId = $this->context->getResponse()->getRenderId();
        $views    = $this->_mDfnObj->getViews();
        if (!empty($renderId) && !empty($views)) {
            if ($renderId === Response::DEFAULT_MOBILE_RENDER_ID && !isset($views[$renderId])) {
                $renderId = Response::DEFAULT_RENDER_ID;
            }
            return $this->_mDfnObj->getView($renderId);
        }
        return null;
    }

    /**
     * CacheContent
     *
     * @param array $view     view setting
     * @param string $content content
     *
     * @return void
     */
    protected function CacheContent($view, $content)
    {
        if (isset($view['cache'])) {
            if (isset($view['expire'])) {
                $expire = $view['expire'];
            } else if (is_string($view['cache']) or is_numeric($view['cache'])) {
                $expire = intval($view['cache']);
            } else {
                $expire = 0;
            }
            $cacheParams = [];
            if (is_array($view['cache'])) {
                $cacheParams = \loeye\lib\ModuleParse::parseInput($view['cache'], $this->context);
            }
            \loeye\base\Utils::setPageCache($this->context->getAppConfig(), $this->context->getRequest()->getModuleId(), $content, $expire, $cacheParams);
        }
    }

    /**
     * getContent
     *
     * @param array $view view setting
     *
     * @return string|null
     */
    protected function getContent($view)
    {
        $content = null;
        if (isset($view['cache'])) {
            $cacheParams = [];
            if (is_array($view['cache'])) {
                $cacheParams = \loeye\lib\ModuleParse::parseInput($view['cache'], $this->context);
            }
            $content = \loeye\base\Utils::getPageCache($this->context->getAppConfig(), $this->context->getRequest()->getModuleId(), $cacheParams);
        }
        return $content;
    }

    /**
     * getCacheId
     *
     * @param array $view view setting
     *
     * @return string
     */
    protected function getCacheId($view)
    {
        $cacheId = $this->_mDfnObj->getModuleId();
        if (isset($view['id'])) {
            $cacheId .= '.' . $this->context->get($view['id']);
        }
        return $cacheId;
    }

    /**
     * _excutePlugin
     *
     * @param array   $pluginSetting   plugin setting
     * @param boolean $isParallel      is parallel
     * @param boolean $continueOnError continue on error
     *
     * @return type
     * @throws \loeye\base\Exception
     */
    private function _excutePlugin($pluginSetting, $isParallel = false, $continueOnError = false)
    {
        $pluginList   = array();
        $returnStatus = true;
        if (is_array($pluginSetting)) {
            foreach ($pluginSetting as $plugin) {
                reset($plugin);
                if (!empty($plugin[\loeye\lib\ModuleParse::CONDITION_KEY]) && (\loeye\lib\ModuleParse::conditionResult(
                                $plugin[\loeye\lib\ModuleParse::CONDITION_KEY],
                                $this->context) == false)) {
                    continue;
                }
                $key = key($plugin);
                if (\loeye\lib\ModuleParse::isCondition($key)) {
                    if (\loeye\lib\ModuleParse::groupConditionResult($key, $this->context) == false) {
                        continue;
                    }
                    $result       = $this->_excutePlugin(current($plugin), $isParallel);
                    $returnStatus = $result[0];
                    $opluginList  = $result[1];
                    $pluginList   = $this->_mergePluginList($pluginList, $opluginList);
                    if ($this->proccessMode == LOEYE_PROCESS_MODE__ERROR_EXIT) {
                        return array(
                            $returnStatus,
                            $pluginList,
                        );
                    }
                } else if (\loeye\lib\ModuleParse::isParallel($key)) {
                    if ($isParallel === true) {
                        throw new \loeye\base\Exception(
                                'parallel can not nest',
                                \loeye\error\BusinessException::INVALID_CONFIG_SET_CODE);
                    }
                    $result       = $this->_excutePlugin(current($plugin), true);
                    $returnStatus = $result[0];
                    $opluginList  = $result[1];
                    $returnStatus = $this->_excuteParallelPlugin($opluginList);
                    $pluginList   = $this->_mergePluginList($pluginList, $opluginList);
                    if ($this->proccessMode == LOEYE_PROCESS_MODE__ERROR_EXIT) {
                        return array(
                            $returnStatus,
                            $pluginList,
                        );
                    }
                } else {
                    $pluginList[] = $plugin;
                    if ($isParallel == false) {
                        $setting = \loeye\lib\ModuleParse::parseInput($plugin, $this->context);
                        if (isset($setting['inputs'])) {
                            $this->_setArrayInContext($setting['inputs']);
                        }
                        if (isset($setting['check_cache'])) {
                            if (\loeye\base\Utils::checkContenxtCacheData($this->context, [], $setting['check_cache'])) {
                                continue;
                            }
                        } else {
                            if (\loeye\base\Utils::checkContenxtCacheData($this->context, $setting)) {
                                continue;
                            }
                        }
                        $pluginObj = \loeye\base\Factory::getPlugin($plugin);
                        if ($pluginObj instanceof \loeye\std\ParallelPlugin) {
                            $pluginObj->prepare($this->context, $setting);
                            $this->context->getParallelClientManager()->excute();
                            $this->context->getParallelClientManager()->reset();
                        }
                        $returnStatus = $pluginObj->process($this->context, $setting);
                        if ($this->proccessMode == LOEYE_PROCESS_MODE__TEST) {
                            $this->_setTraceDataIntoContext($plugin);
                        }
                    }
                }
                $breakStatus = $this->_handleError($plugin);
                if ($breakStatus === true) {
                    return array(
                        $returnStatus,
                        $pluginList,
                    );
                }


                if ($returnStatus === false) {
                    if ($continueOnError === false) {
                        return array(
                            $returnStatus,
                            $pluginList,
                        );
                    }
                }
            }
        }
        return array(
            $returnStatus,
            $pluginList,
        );
    }

    /**
     * _excuteParallelPlugin
     *
     * @param array   $pluginList      plugin list
     * @param boolean $continueOnError continue on error
     *
     * @return boolean
     * @throws \loeye\base\Exception
     */
    private function _excuteParallelPlugin($pluginList, $continueOnError = false)
    {
        $returnStatus  = true;
        $pluginObjList = array();
        $settingList   = array();
        foreach ($pluginList as $id => $plugin) {
            $setting = \loeye\lib\ModuleParse::parseInput($plugin, $this->context);
            if (isset($setting['inputs'])) {
                $this->_setArrayInContext($setting['inputs']);
            }
            if (isset($setting['check_cache'])) {
                if (\loeye\base\Utils::checkContenxtCacheData($this->context, [], $setting['check_cache'])) {
                    continue;
                }
            } else {
                if (\loeye\base\Utils::checkContenxtCacheData($this->context, $setting)) {
                    continue;
                }
            }
            $pluginObj = \loeye\base\Factory::getPlugin($plugin);
            if (!($pluginObj instanceof \loeye\std\ParallelPlugin)) {
                throw new \loeye\base\Exception(
                        'plugin of parallel must ParallelPlugin instance',
                        \loeye\error\BusinessException::INVALID_PLUGIN_INSTANCE_CODE
                );
            }
            $pluginObj->prepare($this->context, $setting);
            $pluginObjList[$id] = $pluginObj;
            $settingList[$id]   = $setting;
        }

        $this->context->getParallelClientManager()->excute();
        $this->context->getParallelClientManager()->reset();

        foreach ($pluginObjList as $id => $pluginObj) {
            $setting      = $settingList[$id];
            $returnStatus = $pluginObj->process($this->context, $setting);

            $breakStatus = $this->_handleError($pluginList[$id]);
            if ($breakStatus === true) {
                return false;
            }

            if ($returnStatus === false) {
                if ($continueOnError === false) {
                    return false;
                }
            }
        }

        if ($this->proccessMode == LOEYE_PROCESS_MODE__TEST) {
            $this->_setTraceDataIntoContext($pluginList);
        }
        return $returnStatus;
    }

    /**
     * _mergePluginList
     *
     * @param array $pluginList1 plugin list 1
     * @param array $pluginList2 plugin list 2
     *
     * @return array
     */
    private function _mergePluginList($pluginList1, $pluginList2)
    {
        foreach ($pluginList2 as $plugin) {
            $pluginList1[] = $plugin;
        }
        return $pluginList1;
    }

    /**
     * _setArrayInContext
     *
     * @param array $inputs inputs
     *
     * @return void
     */
    private function _setArrayInContext($inputs)
    {
        if (is_array($inputs)) {
            foreach ($inputs as $key => $value) {
                $this->context->set($key, $value);
            }
        }
    }

    /**
     * _handleError
     *
     * @param array $plugin plugin setting
     *
     * @return void
     */
    private function _handleError($plugin)
    {
        if (isset($plugin[LOEYE_PLUGIN_HAS_ERROR])) {
            foreach ($plugin[LOEYE_PLUGIN_HAS_ERROR] as $key => $errorSetting) {
                if ($key == 'default') {
                    $errors = $this->context->getErrors();
                } else {
                    $errors = $this->context->getErrors($key);
                }
                if (empty($errors)) {
                    continue;
                }
                $type = 'error';
                (!empty($errorSetting['type'])) && $type = $errorSetting['type'];
                switch ($type) {
                    case 'view':
                        if (!empty($errorSetting['render_id'])) {
                            $this->context->getResponse()->setRenderId($errorSetting['render_id']);
                        }
                        break;
                    case 'url':
                        $url = $this->_getRedirectUrl($errorSetting);
                        if (!empty($url)) {
                            $this->context->getResponse()->setRedirectUrl($url);
                        } else {
                            throw new \loeye\error\BusinessException(
                                    \loeye\error\BusinessException::INVALID_RENDER_SET_MSG,
                                    \loeye\error\BusinessException::INVALID_RENDER_SET_CODE);
                        }
                        break;
                    case 'json':
                    case 'xml':
                        $this->_output($errorSetting);
                        $this->context->getResponse()->setRenderId(null);
                        break;
                    default :
                        $error = current($errors);
                        $code  = 500;
                        if ($error instanceof \Exception) {
                            $message = $error->getMessage();
                            $code    = $error->getCode();
                        } else {
                            $message = is_array($error) ? print_r($errors, true) : $error;
                        }
                        if (isset($errorSetting['code'])) {
                            $code = $errorSetting['code'];
                        }
                        if (isset($errorSetting['message'])) {
                            $message = $errorSetting['message'];
                        }
                        $error        = new \Exception($message, $code);
                        $file         = !empty($errorSetting['page']) ? $errorSetting['page'] : null;
                        $errorContent = \loeye\base\Factory::includeErrorPage($this->context, $error, $file);
                        $this->context->getResponse()->addOutput($errorContent);
                        $this->context->getResponse()->setRenderId(null);
                        break;
                }
                $this->proccessMode = LOEYE_PROCESS_MODE__ERROR_EXIT;
                return true;
            }
        }
        return false;
    }

    /**
     * _output
     *
     * @param array $errorSetting error setting
     *
     * @return void
     */
    private function _output($errorSetting)
    {
        $this->context->getResponse()->setFormat($errorSetting['type']);
        $status     = \loeye\base\Utils::getData($errorSetting, 'code', 200);
        $this->context->getResponse()->addOutput($status, 'status');
        $message    = \loeye\base\Utils::getData($errorSetting, 'msg', 'OK');
        $this->context->getResponse()->addOutput($message, 'message');
        $data       = array();
        $outDataKey = \loeye\base\Utils::getData($errorSetting, 'data', null);
        if (!empty($outDataKey)) {
            $data = \loeye\base\Utils::getData($this->context, $outDataKey);
        } else if (isset($errorSetting['error'])) {
            $data = $this->context->getErrors($errorSetting['error']);
        }
        $this->context->getResponse()->addOutput($data, 'data');
        $url = $this->_getRedirectUrl($errorSetting);
        if (!empty($url)) {
            $this->context->getResponse()->addOutput($url, 'redirect');
        }
    }

    /**
     * _getRedirectUrl
     *
     * @param array $errorSetting error setting
     *
     * @return string
     */
    private function _getRedirectUrl($errorSetting)
    {
        $url       = null;
        $routerKey = \loeye\base\Utils::getData($errorSetting, 'router_key');
        if (!empty($routerKey) && $this->context->getRouter() instanceof Router) {
            $parameter = \loeye\base\Utils::getData($errorSetting, 'params', array());
            $router    = $this->context->getRouter();
            $url       = $router->generate($routerKey, $parameter);
        } else {
            $url = \loeye\base\Utils::getData($errorSetting, 'url');
        }
        return $url;
    }

    /**
     * _setTimezone
     *
     * @return void
     */
    private function _setTimezone()
    {
        $timezone = $this->context->getAppConfig()->getSetting('configuration.timezone', 'UTC');
        $this->context->getAppConfig()->setTimezone($timezone);
        date_default_timezone_set($timezone);
    }

    /**
     * _initComponent
     *
     * @return void
     */
    private function _initComponent()
    {
        $component = $this->context->getAppConfig()->getSetting('component');
        if (!empty($component)) {
            foreach ((array) $component as $item => $list) {
                if ($item == 'namespace') {
                    foreach ($list as $ns => $path) {
                        if (is_array($path)) {
                            array_reduce($path, function ($ns, $item) {
                                \loeye\base\AutoLoadRegister::addNamespace($ns, $item);
                                return $ns;
                            }, $ns);
                        } else {
                            \loeye\base\AutoLoadRegister::addNamespace($ns, $path);
                        }
                    }
                } else if ($item == 'alias') {
                    foreach ($list as $as => $path) {
                        if (is_array($path)) {
                            array_reduce($path, function ($as, $item) {
                                \loeye\base\AutoLoadRegister::addAlias($as, $item);
                                return $as;
                            }, $ns);
                        } else {
                            \loeye\base\AutoLoadRegister::addNamespace($as, $path);
                        }
                    }
                } else {
                    if (is_array($list)) {
                        foreach ($list as $dir => $ignore) {
                            \loeye\base\Factory::autoload($dir, $ignore);
                        }
                    } else {
                        \loeye\base\AutoLoadRegister::addDir($list);
                    }
                }
            }
        }
    }

    /**
     * parseUrl
     *
     * @param string $moduleId module id
     *
     * @return void
     */
    protected function parseUrl($moduleId = null)
    {
        if (empty($moduleId)) {
            if (filter_has_var(INPUT_GET, 'm_id')) {
                $moduleId = filter_input(INPUT_GET, 'm_id', FILTER_SANITIZE_STRING);
            } else {
                if ($this->context->getUrlManager() instanceof \loeye\base\UrlManager) {
                    $moduleId = $this->context->getUrlManager()->match(filter_input(INPUT_SERVER, 'REQUEST_URI'));
                } else {
                    if (filter_has_var(INPUT_SERVER, 'REDIRECT_routerDir')) {
                        $routerDir = filter_input(INPUT_SERVER, 'REDIRECT_routerDir', FILTER_SANITIZE_STRING);
                    } else if (filter_has_var(INPUT_SERVER, 'routerDir')) {
                        $routerDir = filter_input(INPUT_SERVER, 'routerDir', FILTER_SANITIZE_STRING);
                    } else if (filter_has_var(INPUT_GET, 'routerDir')) {
                        $routerDir = filter_input(INPUT_GET, 'routerDir', FILTER_SANITIZE_STRING);
                    } else {
                        $routerDir = PROJECT_NAMESPACE;
                    }
                    $moduleId = $this->_excuteRouter($routerDir);
                }
            }
        }
        if (empty($moduleId)) {
            $uri = (isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '');
            throw new \loeye\error\ResourceException(\loeye\error\ResourceException::PAGE_NOT_FOUND_MSG, \loeye\error\ResourceException::PAGE_NOT_FOUND_CODE);
        }
        return $moduleId;
    }

    /**
     * _addResource
     *
     * @param string $type     type
     * @param mixed  $resource resource
     *
     * @return void
     */
    private function _addResource($type, $resource)
    {
        $res = new Resource($type, $resource);
        $this->context->getResponse()->addResource($res);
    }

}
