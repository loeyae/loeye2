<?php

/**
 * RequestParameterException.php
 *
 * PHP version 7
 *
 * Licensed under the Apache License, Version 2.0 (the "License"),
 * see LICENSE for more details: http://www.apache.org/licenses/LICENSE-2.0.
 *
 * @category PHP
 * @package  LOEYE
 * @author   Zhang Yi <loeyae@gmail.com>
 * @version  2018-04-08 11:10:07
 * @link     https://github.com/loeyae/loeye2.git
 */
namespace loeye\error;
/**
 * ParameterException
 *
 * @author   Zhang Yi <loeyae@gmail.com>
 */
class RequestParameterException extends \loeye\base\Exception
{

    /**
     * default error code
     */
    const DEFAULT_ERROR_CODE = 410000;

    /**
     * default error message
     */
    const DEFAULT_ERROR_MSG = "Request Parameter Error";

    const REQUEST_BODY_EMPTY_CODE = 410100;

    const REQUEST_BODY_EMPTY_MSG = "Request Body Not Allowed Empty";

    const REQUEST_PARAMETER_ERROR_CODE = 410200;

    public static $PARAMETER_ERROR_MSG_TEMPLATES = [
        'path_var_error' => 'Path Variable {field} Not Null',
        '',
    ];

}