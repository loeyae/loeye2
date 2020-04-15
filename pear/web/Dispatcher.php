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

use loeye\base\Exception;
use loeye\base\Factory;
use loeye\base\ModuleDefinition;
use loeye\base\Router;
use loeye\base\UrlManager;
use loeye\base\Utils;
use loeye\error\BusinessException;
use loeye\error\ResourceException;
use loeye\lib\ModuleParse;
use loeye\std\ParallelPlugin;
use Psr\Cache\InvalidArgumentException;
use ReflectionException;
use Symfony\Component\Cache\Exception\CacheException;
use Throwable;
use function loeye\base\ExceptionHandler;

define('LOEYE_PLUGIN_HAS_ERROR', 'lyHasError');

/**
 * Description of Dispatcher
 *
 * @author   Zhang Yi <loeyae@gmail.com>
 */
class Dispatcher extends \loeye\std\Dispatcher
{

    /**
     * @var ModuleDefinition
     */
    private $_mDfnObj;


    /**
     * dispatch
     *
     * @param mixed $moduleId module id
     *
     * @return void
     */
    public function dispatch($moduleId = null): void
    {
        try {
            $moduleId = $this->parseUrl($moduleId);
            $this->initIOObject($moduleId);
            if (empty($moduleId)) {
                $moduleId = $this->context->getRequest()->getModuleId();
            }

            if (empty($moduleId)) {
                throw new ResourceException(ResourceException::PAGE_NOT_FOUND_MSG,
                    ResourceException::PAGE_NOT_FOUND_CODE);
            }
            $this->initAppConfig();
            $this->initConfigConstants();
            $this->initLogger();
            $this->setTimezone();
            $this->initComponent();
            $this->_mDfnObj = new ModuleDefinition($this->context->getAppConfig(), $moduleId);

            $this->executeModule();

            $this->redirectUrl();
            $view = $this->getView();
            $this->executeView($view);
            $this->executeOutput();
        } catch (InvalidArgumentException $e) {
            ExceptionHandler($e, $this->context);
        } catch (Throwable $e) {
            ExceptionHandler($e, $this->context);
        } finally {
            if ($this->processMode > LOEYE_PROCESS_MODE__NORMAL) {
                $this->setTraceDataIntoContext(array());
                Utils::logContextTrace($this->context, null, false);
            }
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
    public function setUrlManager(array $setting): void
    {
        $router = new UrlManager($setting);
        $this->context->setRouter($router);
    }


    /**
     * execute module
     *
     * @return void
     * @throws Exception
     * @throws ReflectionException
     * @throws InvalidArgumentException
     * @throws Throwable
     */
    protected function executeModule(): void
    {
        $this->context->setModule($this->_mDfnObj);
        $this->context->setParallelClientManager(Factory::parallelClientManager());

        $inputs = $this->_mDfnObj->getInputs();
        $this->_setArrayInContext($inputs);
        $setting = ModuleParse::parseInput($this->_mDfnObj->getSetting(), $this->context);
        $continueOnError = false;
        if (isset($setting['continue_on_error']) && $setting['continue_on_error'] === 'true') {
            $continueOnError = true;
        }
        $cacheAble = true;
        if (isset($setting['cache_able'])) {
            $cacheAble = ModuleParse::conditionResult($setting['cache_able'], $this->context);
        }
        if ($cacheAble && isset($setting['cache'])) {
            $this->context->setExpire($setting['cache']);
        }
        if ($cacheAble) {
            $this->context->loadCacheData();
        }

        if ($this->processMode > LOEYE_PROCESS_MODE__NORMAL) {
            $this->setTraceDataIntoContext(array());
        }
        $mockMode = $this->context->getRequest()->getParameterGet('ly_p_m');
        if ($this->processMode > LOEYE_PROCESS_MODE__NORMAL && $mockMode === 'mock') {
            $mockPlugins = $this->_mDfnObj->getMockPlugins();
            [$returnStatus] = $this->_executePlugin($mockPlugins, false, true);
        } else {
            $plugins = $this->_mDfnObj->getPlugins();
            [$returnStatus] = $this->_executePlugin($plugins, false, $continueOnError);
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
    protected function initIOObject($moduleId): void
    {
        $request = Factory::request($moduleId);
        $request->setRouter($this->context->getRouter());
        $this->context->setRequest($request);

        $response = Factory::response();
        if (defined('MOBILE_RENDER_ENABLE') && MOBILE_RENDER_ENABLE && $request->getDevice()) {
            $response->setRenderId(Response::DEFAULT_MOBILE_RENDER_ID);
        }
        $this->context->setResponse($response);
    }


    /**
     * _executeRouter
     *
     * @param string $routerDir router dir
     *
     * @return string|null
     * @throws BusinessException
     */
    private function _executeRouter($routerDir): ?string
    {
        $moduleId = null;
        $router = new Router($routerDir);
        $this->context->setRouter($router);
        if (filter_has_var(INPUT_GET, 'm_id')) {
            $moduleId = filter_input(INPUT_GET, 'm_id', FILTER_SANITIZE_STRING);
        } else {
            $requestUrl = filter_input(INPUT_SERVER, 'REQUEST_URI');
            $moduleId = $router->match($requestUrl);
        }
        return $moduleId;
    }


    /**
     * getView
     *
     * @return array|null
     * @throws Exception
     */
    protected function getView(): ?array
    {
        $renderId = $this->context->getResponse()->getRenderId();
        $views = $this->_mDfnObj->getViews();
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
     * @param array $view view setting
     * @param string $content content
     *
     * @return void
     * @throws Exception
     * @throws CacheException
     */
    protected function CacheContent($view, $content): void
    {
        if (isset($view['cache'])) {
            if (isset($view['expire'])) {
                $expire = $view['expire'];
            } else if (is_string($view['cache']) || is_numeric($view['cache'])) {
                $expire = (int)$view['cache'];
            } else {
                $expire = 0;
            }
            $cacheParams = [];
            if (is_array($view['cache'])) {
                $cacheParams = ModuleParse::parseInput($view['cache'], $this->context);
            }
            Utils::setPageCache($this->context->getAppConfig(), $this->context->getRequest()->getModuleId(), $content, $expire, $cacheParams);
        }
    }


    /**
     * getContent
     *
     * @param array $view view setting
     *
     * @return string|null
     * @throws CacheException
     * @throws Exception
     * @throws InvalidArgumentException
     */
    protected function getContent($view): ?string
    {
        $content = null;
        if (isset($view['cache'])) {
            $cacheParams = [];
            if (is_array($view['cache'])) {
                $cacheParams = ModuleParse::parseInput($view['cache'], $this->context);
            }
            $content = Utils::getPageCache($this->context->getAppConfig(), $this->context->getRequest()->getModuleId(), $cacheParams);
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
    protected function getCacheId($view): string
    {
        $cacheId = $this->_mDfnObj->getModuleId();
        if (isset($view['id'])) {
            $cacheId .= '.' . $this->context->get($view['id']);
        }
        return $cacheId;
    }


    /**
     * _executePlugin
     *
     * @param array $pluginSetting plugin setting
     * @param boolean $isParallel is parallel
     * @param boolean $continueOnError continue on error
     *
     * @return array
     * @throws Exception
     * @throws ReflectionException
     * @throws Throwable
     */
    private function _executePlugin($pluginSetting, $isParallel = false, $continueOnError = false): array
    {
        $pluginList = array();
        $returnStatus = true;
        if (is_array($pluginSetting)) {
            foreach ($pluginSetting as $plugin) {
                reset($plugin);
                if (!empty($plugin[ModuleParse::CONDITION_KEY]) && (ModuleParse::conditionResult(
                            $plugin[ModuleParse::CONDITION_KEY],
                            $this->context) === false)) {
                    continue;
                }
                $key = key($plugin);
                if (ModuleParse::isCondition($key)) {
                    if (ModuleParse::groupConditionResult($key, $this->context) === false) {
                        continue;
                    }
                    $result = $this->_executePlugin(current($plugin), $isParallel);
                    [$returnStatus, $oPluginList] = $result;
                    $pluginList = $this->_mergePluginList($pluginList, $oPluginList);
                    if ($this->processMode === LOEYE_PROCESS_MODE__ERROR_EXIT) {
                        return array(
                            $returnStatus,
                            $pluginList,
                        );
                    }
                } else if (ModuleParse::isParallel($key)) {
                    if ($isParallel === true) {
                        throw new Exception(
                            'parallel can not nest',
                            BusinessException::INVALID_CONFIG_SET_CODE);
                    }
                    $result = $this->_executePlugin(current($plugin), true);
                    [, $oPluginList] = $result;
                    $returnStatus = $this->_executeParallelPlugin($oPluginList);
                    $pluginList = $this->_mergePluginList($pluginList, $oPluginList);
                    if ($this->processMode === LOEYE_PROCESS_MODE__ERROR_EXIT) {
                        return array(
                            $returnStatus,
                            $pluginList,
                        );
                    }
                } else {
                    $pluginList[] = $plugin;
                    if ($isParallel === false) {
                        $setting = ModuleParse::parseInput($plugin, $this->context);
                        if (isset($setting['inputs'])) {
                            $this->_setArrayInContext($setting['inputs']);
                        }
                        if ($this->checkContextCacheData($setting)) {
                            continue;
                        }
                        $pluginObj = Factory::getPlugin($plugin);
                        if ($pluginObj instanceof ParallelPlugin) {
                            $pluginObj->prepare($this->context, $setting);
                            $this->context->getParallelClientManager()->execute();
                            $this->context->getParallelClientManager()->reset();
                        }
                        $returnStatus = $pluginObj->process($this->context, $setting);
                        if ($this->processMode > LOEYE_PROCESS_MODE__NORMAL) {
                            $this->setTraceDataIntoContext($plugin);
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


                if (($returnStatus === false) && $continueOnError === false) {
                    return array(
                        $returnStatus,
                        $pluginList,
                    );
                }
            }
        }
        return array(
            $returnStatus,
            $pluginList,
        );
    }


    /**
     * _executeParallelPlugin
     *
     * @param array $pluginList plugin list
     * @param boolean $continueOnError continue on error
     *
     * @return boolean
     * @throws Exception
     * @throws ReflectionException
     * @throws Throwable
     */
    private function _executeParallelPlugin($pluginList, $continueOnError = false): bool
    {
        $returnStatus = true;
        $pluginObjList = array();
        $settingList = array();
        foreach ($pluginList as $id => $plugin) {
            $setting = ModuleParse::parseInput($plugin, $this->context);
            if (isset($setting['inputs'])) {
                $this->_setArrayInContext($setting['inputs']);
            }
            if ($this->checkContextCacheData($setting)) {
                continue;
            }
            $pluginObj = Factory::getPlugin($plugin);
            if (!($pluginObj instanceof ParallelPlugin)) {
                throw new Exception(
                    'plugin of parallel must ParallelPlugin instance',
                    BusinessException::INVALID_PLUGIN_INSTANCE_CODE
                );
            }
            $pluginObj->prepare($this->context, $setting);
            $pluginObjList[$id] = $pluginObj;
            $settingList[$id] = $setting;
        }

        $this->context->getParallelClientManager()->execute();
        $this->context->getParallelClientManager()->reset();

        foreach ($pluginObjList as $id => $pluginObj) {
            $setting = $settingList[$id];
            $returnStatus = $pluginObj->process($this->context, $setting);

            $breakStatus = $this->_handleError($pluginList[$id]);
            if ($breakStatus === true) {
                return false;
            }

            if (($returnStatus === false) && $continueOnError === false) {
                return false;
            }
        }

        if ($this->processMode > LOEYE_PROCESS_MODE__NORMAL) {
            $this->setTraceDataIntoContext($pluginList);
        }
        return $returnStatus;
    }

    /**
     * checkContextCacheData
     *
     * @param $setting
     * @return bool
     */
    protected function checkContextCacheData($setting): bool
    {
        if (isset($setting['check_cache'])) {
            if (Utils::checkContextCacheData($this->context, [], $setting['check_cache'])) {
                return true;
            }
        } else if (Utils::checkContextCacheData($this->context, $setting)) {
            return true;
        }
        return false;
    }


    /**
     * _mergePluginList
     *
     * @param array $pluginList1 plugin list 1
     * @param array $pluginList2 plugin list 2
     *
     * @return array
     */
    private function _mergePluginList($pluginList1, $pluginList2): array
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
    private function _setArrayInContext($inputs): void
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
     * @return bool
     * @throws BusinessException
     * @throws Exception
     */
    private function _handleError($plugin): bool
    {
        if (isset($plugin[LOEYE_PLUGIN_HAS_ERROR])) {
            foreach ($plugin[LOEYE_PLUGIN_HAS_ERROR] as $key => $errorSetting) {
                if ($key === 'default') {
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
                            throw new BusinessException(
                                BusinessException::INVALID_RENDER_SET_MSG,
                                BusinessException::INVALID_RENDER_SET_CODE);
                        }
                        break;
                    case 'json':
                    case 'xml':
                        $this->_output($errorSetting);
                        $this->context->getResponse()->setRenderId(null);
                        break;
                    default :
                        $error = current($errors);
                        $code = 500;
                        if ($error instanceof \Exception) {
                            $message = $error->getMessage();
                            $code = $error->getCode();
                        } else {
                            $message = is_array($error) ? print_r($errors, true) : $error;
                        }
                        if (isset($errorSetting['code'])) {
                            $code = $errorSetting['code'];
                        }
                        if (isset($errorSetting['message'])) {
                            $message = $errorSetting['message'];
                        }
                        $error = new \Exception($message, $code);
                        $file = !empty($errorSetting['page']) ? $errorSetting['page'] : null;
                        $errorContent = Factory::includeErrorPage($this->context, $error, $file);
                        $this->context->getResponse()->addOutput($errorContent);
                        $this->context->getResponse()->setRenderId(null);
                        break;
                }
                $this->processMode = LOEYE_PROCESS_MODE__ERROR_EXIT;
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
    private function _output($errorSetting): void
    {
        $this->context->getResponse()->setFormat($errorSetting['type']);
        $status = Utils::getData($errorSetting, 'code', 200);
        $this->context->getResponse()->addOutput($status, 'status');
        $message = Utils::getData($errorSetting, 'msg', 'OK');
        $this->context->getResponse()->addOutput($message, 'message');
        $data = array();
        $outDataKey = Utils::getData($errorSetting, 'data', null);
        if (!empty($outDataKey)) {
            $data = Utils::getData($this->context, $outDataKey);
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
    private function _getRedirectUrl($errorSetting): string
    {
        $url = null;
        $routerKey = Utils::getData($errorSetting, 'router_key');
        if (!empty($routerKey) && $this->context->getRouter() instanceof Router) {
            $parameter = Utils::getData($errorSetting, 'params', array());
            $router = $this->context->getRouter();
            $url = $router->generate($routerKey, $parameter);
        } else {
            $url = Utils::getData($errorSetting, 'url');
        }
        return $url;
    }


    /**
     * parseUrl
     *
     * @param string $moduleId module id
     *
     * @return string
     * @throws BusinessException
     */
    protected function parseUrl($moduleId = null): string
    {
        if (empty($moduleId)) {
            if ($this->context->getRouter() instanceof UrlManager) {
                $moduleId = $this->context->getRouter()->match(filter_input(INPUT_SERVER, 'REQUEST_URI'));
            } else {
                if (filter_has_var(INPUT_SERVER, 'REDIRECT_routerDir')) {
                    $routerDir = filter_input(INPUT_SERVER, 'REDIRECT_routerDir', FILTER_SANITIZE_STRING);
                } else if (filter_has_var(INPUT_SERVER, 'routerDir')) {
                    $routerDir = filter_input(INPUT_SERVER, 'routerDir', FILTER_SANITIZE_STRING);
                } else if (filter_has_var(INPUT_GET, 'routerDir')) {
                    $routerDir = filter_input(INPUT_GET, 'routerDir', FILTER_SANITIZE_STRING);
                } else {
                    $routerDir = defined('PROJECT_PROPERTY') ? PROJECT_PROPERTY : PROJECT_NAMESPACE;
                }
                $moduleId = $this->_executeRouter($routerDir);
            }
        }
        return $moduleId;
    }


    /**
     * _addResource
     *
     * @param string $type type
     * @param mixed $resource resource
     *
     * @return void
     * @throws BusinessException
     */
    protected function _addResource($type, $resource): void
    {
        $res = new Resource($type, $resource);
        $this->context->getResponse()->addResource($res);
    }

}
