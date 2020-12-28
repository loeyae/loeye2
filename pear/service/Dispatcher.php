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

namespace loeye\service;

use loeye\base\AppConfig;
use loeye\base\UrlManager;
use loeye\base\Utils;
use loeye\error\ResourceException;
use loeye\error\ValidateError;
use ReflectionClass;
use Throwable;

/**
 * Dispatcher
 *
 * @author   Zhang Yi <loeyae@gmail.com>
 */
class Dispatcher extends \loeye\std\Dispatcher
{
    public const KEY_MODULE = 'module';
    public const KEY_SERVICE = 'service';
    public const KEY_HANDLER = 'handler';
    public const KEY_REWRITE = 'rewrite';

    /**
     * config
     *
     * @var AppConfig
     */
    protected $config;

    protected $module;
    protected $service;
    protected $handler;
    protected $rewrite;

    /**
     * dispatch
     *
     * @params string $moduleId
     *
     * @param null $moduleId
     * @return void
     */
    public function dispatch($moduleId = null): void
    {
        try {
            $this->parseUrl();
            $this->initIOObject($moduleId ?? $this->module);
            $this->initAppConfig();
            $this->initConfigConstants();
            $this->initLogger();
            $this->initComponent();
            $this->setTimezone();
            $handlerNamespace = $this->context->getAppConfig()->getSetting('handler_namespace', '');
            if (!$handlerNamespace) {
                $handlerNamespace = PROJECT_NAMESPACE . '\\services\\handler';
            }
            $handler = $handlerNamespace . ($this->module ? '\\'. $this->module : '')
                . ($this->service ? '\\' . $this->service : '')
                . '\\' . ucfirst($this->handler) . ucfirst(self::KEY_HANDLER);
            if (!class_exists($handler)) {
                throw new ResourceException(ResourceException::PAGE_NOT_FOUND_MSG, ResourceException::PAGE_NOT_FOUND_CODE);
            }
            $ref = new ReflectionClass($handler);
            $handlerObject = $ref->newInstance($this->context);
            if (!($handlerObject instanceof Handler)) {
                throw new ResourceException(ResourceException::PAGE_NOT_FOUND_MSG, ResourceException::PAGE_NOT_FOUND_CODE);
            }
            $handlerObject->handle();
        } catch (ValidateError $exc) {
            $request = ($this->getContext()->getRequest() ?? new Request());
            $response = ($this->getContext()->getResponse() ?? new Response($request));
            $format = ($request->getFormatType());
            if (empty($format)) {
                $response->setFormat('json');
            }
            $response->setStatusCode(LOEYE_REST_STATUS_OK);
            $response->setStatusMessage('Ok');
            $response->addOutput(
                ['code' => $exc->getCode(), 'message' => $exc->getMessage()], 'status');
            $response->addOutput($exc->getValidateMessage(), 'data');
        } catch (Throwable $exc) {
            Utils::errorLog($exc);
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
        } finally {
            if ($this->processMode > LOEYE_PROCESS_MODE__NORMAL) {
                $this->setTraceDataIntoContext(array());
                Utils::logContextTrace($this->context, null, false);
            }
            $this->executeOutput();
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
    public function init(array $setting): void
    {
        isset($setting[self::KEY_MODULE]) && $this->module = $setting[self::KEY_MODULE];
        isset($setting[self::KEY_SERVICE]) && $this->service = $setting[self::KEY_SERVICE];
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
    protected function initIOObject($moduleId): void
    {
        $request = Request::createFromGlobals();
        $request->setModuleId($moduleId);
        $request->setRouter($this->context->getRouter());
        $this->context->setRequest($request);
        $response = Response::create($request);
        $response->setFormat($request->getFormatType());
        $this->context->setResponse($response);
    }

    /**
     * parseUrlPath
     *
     * @return void
     * @throws ResourceException
     */
    protected function parseUrl(): void
    {
        $requestUrl = $this->context->getRequest()->getRequestUri();
        $path = null;
        if ($this->rewrite) {
            $router = new UrlManager($this->rewrite);
            $this->context->setRouter($router);
            $path = $router->match($requestUrl);
            if ($path === false) {
                throw new ResourceException(ResourceException::PAGE_NOT_FOUND_MSG, ResourceException::PAGE_NOT_FOUND_CODE);
            }
        }
        if ($path === null) {
            $path = parse_url($requestUrl, PHP_URL_PATH);
        }
        $parts = explode('/', trim($path, '/'));
        if (isset($parts[2])) {
            $this->module = $parts[0];
            $this->service = $parts[1];
            $this->handler = Utils::camelize($parts[2]);
        } else if (isset($parts[1])) {
            $this->service = $parts[0];
            $this->handler = Utils::camelize($parts[1]);
        } else {
            $this->handler = Utils::camelize($parts[0]);
        }
        if (empty($this->module) && empty($this->service) && empty($this->handler)) {
            throw new ResourceException(ResourceException::PAGE_NOT_FOUND_MSG, ResourceException::PAGE_NOT_FOUND_CODE);
        }
        $moduleKey = UrlManager::REWRITE_KEY_PREFIX . self::KEY_MODULE;
        $serviceKey = UrlManager::REWRITE_KEY_PREFIX . self::KEY_SERVICE;
        $_GET[$moduleKey] = $this->module;
        $_GET[$serviceKey] = $this->service;
    }

}
