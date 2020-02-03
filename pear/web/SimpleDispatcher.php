<?php

/**
 * SimpleDispatcher.php
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

/**
 * SimpleDispatcher
 *
 * @author   Zhang Yi <loeyae@gmail.com>
 */
class SimpleDispatcher extends \loeye\std\Dispatcher
{

    const KEY_MODULE             = 'module';
    const KEY_CONTROLLER         = 'controller';
    const KEY_ACTION             = 'action';
    const KEY_REWRITE            = 'rewrite';
    const KEY_REQUEST_URI        = 'u';
    const KEY_REQUEST_MODULE     = 'm';
    const KEY_REQUEST_CONTROLLER = 'c';
    const KEY_REQUEST_ACTION     = 'a';

    protected $module;
    protected $controller;
    protected $action;
    protected $rewrite;

    /**
     * dispatcher
     *
     * @return void
     */
    public function dispatche($moduleId = null)
    {
        try {
            $this->parseUrl();
            $this->initIOObject($moduleId ?? $this->module);
            $this->initAppConfig();
            $this->initConfigConstants();
            $this->initLogger();
            $this->setTimezone();
            $this->initComponent();
            $object = $this->excuteModule();
            $this->redirectUrl();

            $view = $this->getView($object);
            $this->excuteView($view);
            $this->excuteOutput();
        } catch (\loeye\base\Exception $exc) {
            \loeye\base\ExceptionHandler($exc, $this->context);
        } catch (\Exception $exc) {
            \loeye\base\ExceptionHandler($exc, $this->context);
        }
        if ($this->proccessMode == LOEYE_PROCESS_MODE__TEST) {
            $this->setTraceDataIntoContext(array());
            \loeye\base\Utils::logContextTrace($this->context);
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
        $response->setFormat($request->getFormatType());
        $this->context->setResponse($response);
    }

    protected function getView(\loeye\std\Controller $object)
    {
        $view = [];
        if (!empty($object->view)) {
            if (is_string($object->view)) {
                $view = ['src' => $object->view];
            } else {
                $view = (array) $object->view;
            }
        }
        if (!empty($object->layout)) {
            $view['layout'] = $object->layout;
        }
        return $view;
    }

    /**
     * excuteModule
     *
     * @throws \loeye\base\Exception
     */
    protected function excuteModule()
    {
        $controllerNamespace = $this->context->getAppConfig()->getSetting('controler_namespace', '');
        if (!$controllerNamespace) {
            $controllerNamespace = PROJECT_NAMESPACE . '\\controllers\\' . mb_convert_case($this->context->getAppConfig()->getPropertyName(), MB_CASE_LOWER);
        }
        $controller = $controllerNamespace . '\\' . ucfirst($this->controller) . ucfirst(self::KEY_CONTROLLER);

        $action = ucfirst($this->action) . ucfirst(self::KEY_ACTION);

        if (!class_exists($controller)) {
            throw new \loeye\error\ResourceException(\loeye\error\ResourceException::PAGE_NOT_FOUND_MSG, \loeye\error\ResourceException::PAGE_NOT_FOUND_CODE);
        }
        $ref    = new \ReflectionClass($controller);
        $object = $ref->newInstance($this->context);
        if (!method_exists($object, $action)) {
            throw new \loeye\error\ResourceException(\loeye\error\ResourceException::PAGE_NOT_FOUND_MSG, \loeye\error\ResourceException::PAGE_NOT_FOUND_CODE);
        }
        $prepare = $object->prepare();
        if ($prepare) {
            $refMethod = new \ReflectionMethod($object, $action);
            $refMethod->invoke($object);
        }
        return $object;
    }

    /**
     * init
     *
     * @param array $setting base conf setting
     * <p>
     * ['module'    => default module,
     * 'controller' => default controller,
     * 'action'     => default action,
     * 'rewrite'    => rewrite rule]
     *
     * rewrite ex: '/<module:\w+>/<controller:\w+>/<action:\w+>.html' => '{module}/{controller}/{action}'
     * </p>
     *
     * @return void
     */
    public function init(array $setting)
    {
        isset($setting[self::KEY_MODULE]) && $this->module     = $setting[self::KEY_MODULE];
        isset($setting[self::KEY_CONTROLLER]) && $this->controller = $setting[self::KEY_CONTROLLER];
        isset($setting[self::KEY_ACTION]) && $this->action     = $setting[self::KEY_ACTION];
        isset($setting[self::KEY_REWRITE]) && $this->rewrite    = $setting[self::KEY_REWRITE];
    }

    /**
     * parseUrl
     *
     * @throws \loeye\base\Exception
     */
    protected function parseUrl()
    {
        $requestUrl = filter_input(INPUT_SERVER, 'REQUEST_URI');
        $path       = null;
        if ($this->rewrite) {
            $router = new \loeye\base\UrlManager($this->rewrite);
            $this->context->setUrlManager($router);
            $path   = $router->match($requestUrl);
            if ($path === false) {
                throw new \loeye\error\ResourceException(\loeye\error\ResourceException::PAGE_NOT_FOUND_MSG, \loeye\error\ResourceException::PAGE_NOT_FOUND_CODE);
            }
        }
        if ($path == null && filter_has_var(INPUT_GET, self::KEY_REQUEST_URI)) {
            $path = filter_input(INPUT_GET, self::KEY_REQUEST_URI);
        }
        if (!empty($path)) {
            $parts = explode('/', trim($path, '/'));
            if (isset($parts[2])) {
                $this->module     = $parts[0];
                $this->controller = \loeye\base\Utils::camelize($parts[1]);
                $this->action     = \loeye\base\Utils::camelize($parts[2]);
            } else if (isset($parts[1])) {
                $this->controller = \loeye\base\Utils::camelize($parts[0]);
                $this->action     = \loeye\base\Utils::camelize($parts[1]);
            } else {
                $this->controller = \loeye\base\Utils::camelize($parts[0]);
            }
        } else {
            if (filter_has_var(INPUT_GET, self::KEY_REQUEST_MODULE)) {
                $this->module = filter_input(INPUT_GET, self::KEY_REQUEST_MODULE);
            }
            if (filter_has_var(INPUT_GET, self::KEY_REQUEST_CONTROLLER)) {
                $this->controller = \loeye\base\Utils::camelize(filter_input(INPUT_GET, self::KEY_REQUEST_CONTROLLER));
            }
            if (filter_has_var(INPUT_GET, self::KEY_REQUEST_ACTION)) {
                $this->action = \loeye\base\Utils::camelize(filter_input(INPUT_GET, self::KEY_REQUEST_ACTION));
            }
        }
        if (empty($this->module) || empty($this->controller)) {
            throw new \loeye\error\ResourceException(\loeye\error\ResourceException::PAGE_NOT_FOUND_MSG, \loeye\error\ResourceException::PAGE_NOT_FOUND_CODE);
        }
        if (empty($this->action)) {
            $this->action = 'index';
        }
    }

    /**
     * cacheContent
     *
     * @param array $view     view setting
     * @param string $content content
     *
     * @return void
     */
    protected function cacheContent($view, $content)
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
    protected function getCacheId($view = array())
    {
        $cacheId = $this->module . '_' . $this->controller . '_' . $this->action;
        if (isset($view['id'])) {
            $cacheId .= '.' . $this->context->get($view['id']);
        }
        return $cacheId;
    }

}
