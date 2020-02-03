<?php

/**
 * BusinessException.php
 *
 * PHP version 7
 *
 * Licensed under the Apache License, Version 2.0 (the "License"),
 * see LICENSE for more details: http://www.apache.org/licenses/LICENSE-2.0.
 *
 * @category PHP
 * @package  LOEYE
 * @author   Zhang Yi <loeyae@gmail.com>
 * @version  2019-4-8 14:43:09
 * @link     https://github.com/loeyae/loeye2.git
 */

namespace loeye\error;

/**
 * BusinessException
 *
 * @author   Zhang Yi <loeyae@gmail.com>
 */
class BusinessException extends \loeye\base\Exception
{

    /**
     * default error code
     */
    const DEFAULT_ERROR_CODE = 500000;

    /**
     * default error message
     */
    const DEFAULT_ERROR_MSG = "Business Error";

    const INVALID_CONFIG_SET_CODE = 500001;
    const INVALID_CONFIG_SET_MSG = "Invalid config %setting%";

    const INVALID_LANGUAGE_SET_CODE = 500002;
    const INVALID_LANGUAGE_SET_MSG = "Invalid language setting";

    const INVALID_PARAMETER_CODE = 500100;
    const INVALID_PARAMETER_MSG = "Invalid paramter";

    const INVALID_MODULE_ID_CODE = 500200;
    const INVALID_MODULE_ID_MSG = "Invalid module id";

    const INVALID_MODULE_SET_CODE = 500200;
    const INVALID_MODULE_SET_MSG = "Invalid module setting: %mode%";

    const INVALID_FILE_TYPE_CODE = 500301;
    const INVALID_FILE_TYPE_MSG = "Invalid file type";

    const PDO_GENERIC_ERROR_CODE = 500400;
    const PDO_GENERIC_ERROR_MSG = "PDO error";

    const PDO_SQL_SETTING_ERROR_CODE = 500401;
    const PDO_SQL_SETTING_ERROR_MSG = "PDO sql setting error";

    const INVALID_PLUGIN_ERROR_CODE = 500500;
    const INVALID_PLUGIN_ERROR_MSG = "Invalid pluging";

    const INVALID_PLUGIN_SET_CODE = 500501;
    const INVALID_PLUGIN_SET_MSG = "Invalid pluging setting";

    const INVALID_PLUGIN_INSTANCE_CODE = 500502;
    const INVALID_PLUGIN_INSTANCE_MSG = "Invalid pluging instance";

    const INVALID_RENDER_SET_CODE = 500501;
    const INVALID_RENDER_SET_MSG = "Invalid render setting";

    public function __construct(string $errorMessage = self::DEFAULT_ERROR_MSG, int $errorCode = self::DEFAULT_ERROR_CODE, $parameter = array())
    {
        parent::__construct($errorMessage, $errorCode, $parameter);
    }

}
