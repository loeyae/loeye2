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

/**
 * ExceptionHandler
 *
 * @param Exception           $exc     exception
 * @param \loeye\base\Context $context context
 *
 * @return void
 */
function ExceptionHandler(\Exception $exc, Context $context)
{
    $format = null;
    if ($context->getRequest() instanceof \loeye\web\Request) {
        $format = $context->getRequest()->getFormatType();
    }
    switch ($format) {
        case 'xml':
        case 'json':
            $response = $context->getResponse();
            if (!$response instanceof \loeye\web\Response) {
                $response = new \loeye\web\Response();
            }
            $response->addOutput(['code' => $exc->getCode(), 'message' => $exc->getMessage()]);
            $renderObj = \loeye\base\Factory::getRender($format);

            $renderObj->header($response);
            $renderObj->output($response);
            break;
        default :
            $errorPage = null;
            if ($context->getModule() instanceof ModuleDefinition) {
                $setting = $context->getModule()->getSetting();
                if (isset($setting['error_page'])) {
                    if (is_array($setting['error_page'])) {
                        $code = $exc->getCode();
                        if (isset($setting['error_page'][$code])) {
                            $errorPage = $setting['error_page'][$code];
                        }
                    } else {
                        $errorPage = PROJECT_ERRORPAGE_DIR . DIRECTORY_SEPARATOR . $setting['error_page'];
                    }
                }
            }
            Factory::includeErrorPage($context, $exc, $errorPage);
            break;
    }
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
    const DEFAULT_ERROR_CODE = 500;

    /**
     * default error message
     */
    const DEFAULT_ERROR_MSG = "Internal Error";

    /**
     * __construct
     *
     * @param string $errorMessage error message
     * @param int    $errorCode    error code
     *
     * @return void
     */
    public function __construct(string $errorMessage = self::DEFAULT_ERROR_MSG, int $errorCode = self::DEFAULT_ERROR_CODE, array $parameter = [])
    {
        if ($parameter) {
            $errorMessage = str_replace(array_keys($parameter), array_values($parameter), $errorMessage);
        }
        parent::__construct($errorMessage, $errorCode);
    }

}
