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
 * @version  GIT: $Id: Zhang Yi $
 * @link     https://github.com/loeyae/loeye.git
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

    const DEFAULT_ERROR_CODE = 500;
    const INVALID_CONFIG_SET_CODE = 500001;
    const INVALID_LANGUAGE_SET_CODE = 500002;
    const INVALID_PARAMETER_CODE = 500100;
    const INVALID_MODULE_ID_CODE = 500200;
    const INVALID_FILE_TYPE_CODE = 500301;
    const PDO_GENERIC_ERROR_CODE = 500400;
    const PDO_SQL_SETTING_ERROR_CODE = 500401;
    const INVALID_PLUGIN_ERROR_CODE = 500500;
    const INVALID_PLUGIN_SET_CODE = 500501;
    const INVALID_PLUGIN_INSTANCE_CODE = 500502;
    const ACCESS_DENIED = 401;
    const LOGIN_FAILED = 401001;
    const ACCESS_UNAPPROVED = 401002;
    const CRUMB_ERROR_CODE = 401003;
    const REPEAT_ERROR_CODE = 401004;
    const PAGE_NOT_FOUND_CODE = 404;
    const MODULE_NOT_FOUND_CODE = 404010;
    const FILE_NOT_FOUND_CODE = 404020;
    const LANGUAGE_FILE_NOT_FOUND_CODE = 404021;
    const RECORD_NOT_FOUND_CODE = 404040;
    const DEFAULT_LOGIC_ERROR = 900;
    const CONTEXT_KEY_NOT_FOUND  = 900101;
    const CONTEXT_VALUE_IS_EMPTY = 900102;
    const DATA_KEY_NOT_FOUND          = 900201;
    const DATA_VALUE_IS_EMPTY         = 900202;
    const DATA_AT_LEAST_EXIST_ONE_KEY = 900203;

    /**
     * __construct
     *
     * @param string $errorMessage error message
     * @param int    $errorCode    error code
     *
     * @return void
     */
    public function __construct($errorMessage, $errorCode = self::DEFAULT_ERROR_CODE)
    {
        parent::__construct($errorMessage, $errorCode);
    }

}
