<?php

/**
 * DAOException.php
 *
 * PHP version 7
 *
 * Licensed under the Apache License, Version 2.0 (the "License"),
 * see LICENSE for more details: http://www.apache.org/licenses/LICENSE-2.0.
 *
 * @category PHP
 * @package  LOEYE
 * @author   Zhang Yi <loeyae@gmail.com>
 * @version  2018-04-08 14:30:14
 * @link     https://github.com/loeyae/loeye2.git
 */
namespace loeye\error;
use loeye\base\Exception;

/**
 * DAOException
 *
 * @author   Zhang Yi <loeyae@gmail.com>
 */
class DAOException extends Exception
{
    /**
     * default error code
     */
    public const DEFAULT_ERROR_CODE = 510000;

    /**
     * default error message
     */
    public const DEFAULT_ERROR_MSG = 'DataBase Error';

    public function __construct(string $errorMessage = self::DEFAULT_ERROR_MSG, int $errorCode =
    self::DEFAULT_ERROR_CODE, $parameter = array())
    {
        parent::__construct($errorMessage, $errorCode, $parameter);
    }

}
