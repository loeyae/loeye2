<?php

/**
 * Exception.php
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

use loeye\render\SegmentRender;
use loeye\web\Request;
use loeye\web\Response;
use ReflectionException;
use Throwable;

/**
 * ExceptionHandler
 *
 * @param Throwable $exc exception
 * @param Context $context context
 *
 * @return void
 */
function ExceptionHandler(Throwable $exc, Context $context)
{
    if (!($exc instanceof Exception)) {
        Logger::exception($exc);
    }
    $format = null;
    $appConfig = $context->getAppConfig();
    if ($context->getRequest() instanceof Request) {
        $format = $appConfig ? $appConfig->getSetting('application.response.format', $context->getRequest()
            ->getFormatType()) : $context->getRequest()->getFormatType();
    }
    $response = $context->getResponse();
    if (!$response instanceof Response) {
        $response = new Response();
    }
    $renderObj = new SegmentRender();
    switch ($format) {
        case 'xml':
        case 'json':
            $debug = $appConfig ? $appConfig->getSetting('debug', false) : false;
            $res = ['status' => ['code' => LOEYE_REST_STATUS_BAD_REQUEST, 'message' => 'Internal Error']];
            if ($debug) {
                $res['data'] = [
                    'code' => $exc->getCode(),
                    'message' => $exc->getMessage(),
                    'traceInfo' => $exc->getTraceAsString(),
                ];
            } else {
                $res['data'] = $exc->getMessage();
            }
            $response->addOutput($res['status'], 'status');
            $response->addOutput($res['data'], 'data');
            try {
                $renderObj = Factory::getRender($format);
            } catch (ReflectionException $e) {
                Logger::exception($e);
            }
            break;
        default :
            $errorPage = null;
            if ($context->getModule() instanceof ModuleDefinition) {
                $setting = $context->getModule()->getSetting();
                if (isset($setting['error_page'])) {
                    $code = $exc->getCode();
                    $errorPage = $setting['error_page'][$code] ?? $setting['error_page']['default'] ?? null;
                }
            }
            $html = Factory::includeErrorPage($context, $exc, $errorPage);
            $response->addOutput($html);
            break;
    }
    $renderObj->header($response);
    $renderObj->output($response);
}

/**
 * Description of Exception
 *
 * @author   Zhang Yi <loeyae@gmail.com>
 */
class Exception extends \Exception
{

    /**
     * default error code
     */
    public const DEFAULT_ERROR_CODE = 500;

    /**
     * default error message
     */
    public const DEFAULT_ERROR_MSG = 'Internal Error';

    /**
     * __construct
     *
     * @param string $errorMessage error message
     * @param int $errorCode error code
     * @param array $parameter
     */
    public function __construct(string $errorMessage = self::DEFAULT_ERROR_MSG, int $errorCode = self::DEFAULT_ERROR_CODE, array $parameter = [])
    {
        $translator = defined('PROJECT_PROPERTY') ? Factory::translator() : new Translator();
        $parameters = [];
        foreach ($parameter as $key => $value) {
            $parameters['%' . $key . '%'] = $value;
        }
        $errorMessage = $translator->getString($errorMessage, $parameters, 'error');
        parent::__construct($errorMessage, $errorCode);
        Logger::trace($errorMessage, $errorCode, __FILE__, __LINE__, Logger::LOEYE_LOGGER_TYPE_ERROR);
    }

}
