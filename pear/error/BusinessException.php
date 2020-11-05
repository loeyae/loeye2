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

use loeye\base\Exception;

/**
 * BusinessException
 *
 * @author   Zhang Yi <loeyae@gmail.com>
 */
class BusinessException extends Exception
{

    /**
     * default error code
     */
    public const DEFAULT_ERROR_CODE = 500000;

    /**
     * default error message
     */
    public const DEFAULT_ERROR_MSG = 'Business Error';

    public const INVALID_CONFIG_SET_CODE = 500001;
    public const INVALID_CONFIG_SET_MSG = 'Invalid config %setting%';

    public const INVALID_LANGUAGE_SET_CODE = 500002;
    public const INVALID_LANGUAGE_SET_MSG = 'Invalid language setting';

    public const INVALID_PARAMETER_CODE = 500100;
    public const INVALID_PARAMETER_MSG = 'Invalid parameter';

    public const INVALID_MODULE_ID_CODE = 500200;
    public const INVALID_MODULE_ID_MSG = 'Invalid module id';

    public const INVALID_MODULE_SET_CODE = 500200;
    public const INVALID_MODULE_SET_MSG = 'Invalid module setting: %mode%';

    public const INVALID_FILE_TYPE_CODE = 500301;
    public const INVALID_FILE_TYPE_MSG = 'Invalid file type';

    public const PDO_GENERIC_ERROR_CODE = 500400;
    public const PDO_GENERIC_ERROR_MSG = 'PDO error';

    public const PDO_SQL_SETTING_ERROR_CODE = 500401;
    public const PDO_SQL_SETTING_ERROR_MSG = 'PDO sql setting error';

    public const INVALID_PLUGIN_ERROR_CODE = 500500;
    public const INVALID_PLUGIN_ERROR_MSG = 'Invalid plugin';

    public const INVALID_PLUGIN_SET_CODE = 500501;
    public const INVALID_PLUGIN_SET_MSG = 'Invalid plugin setting';

    public const INVALID_PLUGIN_INSTANCE_CODE = 500502;
    public const INVALID_PLUGIN_INSTANCE_MSG = 'Invalid plugin instance';

    public const INVALID_RENDER_SET_CODE = 500501;
    public const INVALID_RENDER_SET_MSG = 'Invalid render setting';

    public function __construct(string $errorMessage = self::DEFAULT_ERROR_MSG, int $errorCode =
    self::DEFAULT_ERROR_CODE, $parameter = array())
    {
        parent::__construct($errorMessage, $errorCode, $parameter);
    }

}
