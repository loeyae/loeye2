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
 * @version  GIT: $Id: Zhang Yi $
 * @link     https://github.com/loeyae/loeye.git
 */

namespace loeye\service;

require_once 'Constants.php';

/**
 * Dispatcher
 *
 * @author   Zhang Yi <loeyae@gmail.com>
 */
class Dispatcher extends \loeye\std\Dispatcher
{
    const KEY_MODULE = 'module';
    const KEY_SERVICE = 'service';
    const KEY_HANDLER = 'handler';
    const KEY_REWRITE = 'rewrite';

    /**
     * config
     *
     * @var AppConfig
     */
    protected $config;

    protected $module;
    protected $service;
    protected $handler;
    protected $rewirte;

    /**
     * dispatche
     *
     * @params string $moduleId
     *
     * @return void
     */
    public function dispatche($moduleId=null)
    {
        try {
            $this->parseUrl();
            $this->initIOObject($moduleId ?? $this->module);
            $this->initAppConfig();
            $this->initConfigConstants();
            $this->initComponent();
            $this->setTimezone();
            $handlerNamespace = $this->context->getAppConfig()->getSetting('handler_namespace', '');
            if (!$handlerNamespace) {
                $handlerNamespace = PROJECT_NAMESPACE . '\\services\\handler\\' . mb_convert_case($this->context->getAppConfig()->getPropertyName(), MB_CASE_LOWER);
            }
            $handler = $handlerNamespace .'\\'. $this->service .'\\'. $this->handler . mb_convert_case(self::KEY_HANDLER, MB_CASE_TITLE);
            if (!class_exists($handler)) {
                throw new \loeye\base\Exception('bad request', 404);
            }
            $ref = new \ReflectionClass($handler);
            $handlerObject = $ref->newInstance($this->context);
            $handlerObject->handle();
            $this->excuteOutput();
        } catch (\Exception $exc) {
            \loeye\base\Utils::errorLog($exc);
            $request = ($this->getContext()->getRequest() ?? new Request());
            $response = ($this->getContext()->getResponse() ?? new Response($request));

            $format = ($request->getFormatType());
            if (empty($format)) {
                $response->setFormat('json');
            }
            $response->setStatusCode(LOEYE_REST_STATUS_SERVICE_UNAVAILABLE);
            $response->setStatusMessage('Internal Error');
            $response->addOutput(
                ['code' => $exc->getCode(), 'message' => $exc->getMessage()], 'status');
            $renderObj = \loeye\base\Factory::getRender($response->getFormat());

            $renderObj->header($response);
            $renderObj->output($response);
        }
    }

    /**
     * init
     *
     * @param array $setting base conf setting
     * <p>
     * ['module'    => default module,
     * 'service'     => default service,
     * 'handler'    => default handler,
     * 'rewrite'    => rewrite rule]
     *
     * rewrite ex: '/<module:\w+>/<service:\w+>/<handler:\w+>.html' => '{module}/{service}/{handler}'
     * </p>
     *
     * @return void
     */
    public function init(array $setting)
    {
        isset($setting[self::KEY_MODULE]) && $this->module = $setting[self::KEY_MODULE];
        isset($setting[self::KEY_SERVICE]) && $this->service= $setting[self::KEY_SERVICE];
        isset($setting[self::KEY_HANDLER]) && $this->handler = $setting[self::KEY_HANDLER];
        isset($setting[self::KEY_REWRITE]) && $this->rewrite = $setting[self::KEY_REWRITE];
    }

    /**
     * initIOObject
     *
     * @param string $moduleId module id
     *
     * @return void
     *
     */
    protected function initIOObject($moduleId) {
        $request = new \loeye\service\Request($moduleId);

        $this->context->setRequest($request);
        $response = new \loeye\service\Response($request);
        $response->setFormat($request->getFormatType());
        $this->context->setResponse($response);
    }

    /**
     * parseUrlPath
     *
     * @return array
     * @throws \loeye\base\Exception
     */
    protected function parseUrl()
    {
        $requestUrl = filter_input(INPUT_SERVER, 'REQUEST_URI', FILTER_UNSAFE_RAW);
        $path = null;
        if ($this->rewrite) {
            $router = new \loeye\base\UrlManager($this->rewrite);
            $path = $router->match($requestUrl);
            if ($path === false) {
                throw new \loeye\base\Exception('url not found',
                    \loeye\base\Exception::FILE_NOT_FOUND_CODE);
            }
        }
        if ($path == null) {
            $path = parse_url($requestUrl, PHP_URL_PATH);
        }
        $parts = explode('/', trim($path, '/'));
        if (isset($parts[2])) {
            $this->module = $parts[0];
            $this->service = $parts[1];
            $this->handler = $parts[2];
        } else if (isset($parts[1])) {
            $this->service = $parts[0];
            $this->handler = $parts[1];
        } else {
            $this->handler = $parts[0];
        }
        if (empty($this->module)|| empty($this->service) || empty($this->handler)) {
            throw new \loeye\base\Exception('bad request', 404);
        }
        $handlerArr = explode('_', $this->handler);
        $handlerArr = array_map(function($item) {
            return mb_convert_case($item, MB_CASE_TITLE);
        }, $handlerArr);
        $this->handler = implode('', $handlerArr);
    }

}
