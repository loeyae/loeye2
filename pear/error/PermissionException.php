<?php

/**
 * PermissionException.php
 *
 * PHP version 7
 *
 * Licensed under the Apache License, Version 2.0 (the "License"),
 * see LICENSE for more details: http://www.apache.org/licenses/LICENSE-2.0.
 *
 * @category PHP
 * @package  LOEYE
 * @author   Zhang Yi <loeyae@gmail.com>
 * @version  Expression GitVersion is undefined on line 14, column 16 in Templates/Scripting/LoeyeNewClass.php.
 * @link     https://github.com/loeyae/loeye2.git
 */

namespace loeye\error;

use loeye\base\Exception;

/**
 * PermissionException
 *
 * @author   Zhang Yi <loeyae@gmail.com>
 */
class PermissionException extends Exception
{

    /**
     * default error code
     */
    public const DEFAULT_ERROR_CODE = 430000;

    /**
     * default error message
     */
    public const DEFAULT_ERROR_MSG = 'Permission Error';

    public const ACCESS_DENIED = 401;

    public const LOGIN_FAILED = 401001;

    public const ACCESS_UNAPPROVED = 401002;

    public const CRUMB_ERROR_CODE = 401003;

    public const REPEAT_ERROR_CODE = 401004;

    public function __construct(string $errorMessage = self::DEFAULT_ERROR_MSG, int $errorCode =
    self::DEFAULT_ERROR_CODE, $parameter = array())
    {
        parent::__construct($errorMessage, $errorCode, $parameter);
    }

}
