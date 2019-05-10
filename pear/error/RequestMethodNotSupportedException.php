<?php

/**
 * RequestMethodNotSupportedException.php
 *
 * PHP version 7
 *
 * Licensed under the Apache License, Version 2.0 (the "License"),
 * see LICENSE for more details: http://www.apache.org/licenses/LICENSE-2.0.
 *
 * @category PHP
 * @package  LOEYE
 * @author   Zhang Yi <loeyae@gmail.com>
 * @version  2019-4-8 15:02:01
 * @link     https://github.com/loeyae/loeye2.git
 */

namespace loeye\error;

/**
 * RequestMethodNotSupportedException
 *
 * @author   Zhang Yi <loeyae@gmail.com>
 */
class RequestMethodNotSupportedException extends \loeye\base\Exception
{

    /**
     * default error code
     */
    const DEFAULT_ERROR_CODE = 405;

    /**
     * default error message
     */
    const DEFAULT_ERROR_MSG = "Method Not Allowed";

    public function __construct($errorMessage = self::DEFAULT_ERROR_MSG, $errorCode = self::DEFAULT_ERROR_CODE): void
    {
        parent::__construct($errorMessage, $errorCode);
    }
}
