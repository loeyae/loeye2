<?php

/**
 * DataException.php
 *
 * PHP version 7
 *
 * Licensed under the Apache License, Version 2.0 (the "License"),
 * see LICENSE for more details: http://www.apache.org/licenses/LICENSE-2.0.
 *
 * @category PHP
 * @package  LOEYE
 * @author   Zhang Yi <loeyae@gmail.com>
 * @version  2018-04-08 09:25:40
 * @link     https://github.com/loeyae/loeye2.git
 */
namespace loeye\error;
use loeye\base\Exception;

/**
 * DataException
 *
 * @author   Zhang Yi <loeyae@gmail.com>
 */
class DataException extends Exception
{

    /**
     * default error code
     */
    public const DEFAULT_ERROR_CODE = 420000;

    /**
     * default error message
     */
    public const DEFAULT_ERROR_MSG = 'Data Error';

    public const DATA_NOT_FOUND_ERROR_CODE = 420404;
    public const DATA_NOT_FOUND_ERROR_MSG = 'Data Not Found';

    public const DATA_NOT_EQUALS = 420001;
    public const DATA_NOT_EQUALS_MSG = '%expected% not equals %actual%';

    public const CONTEXT_VALUE_NOT_EQUALS = 420002;
    public const CONTEXT_VALUE_NOT_EQUALS_MSG = '%key% of context not equals %expected%';

    public const ARRAY_VALUE_NOT_EQUALS = 420003;
    public const ARRAY_VALUE_NOT_EQUALS_MSG = '%key% of %data% not equals %expected%';

    public function __construct(string $errorMessage = self::DEFAULT_ERROR_MSG, int $errorCode =
    self::DEFAULT_ERROR_CODE, $parameter = array())
    {
        parent::__construct($errorMessage, $errorCode, $parameter);
    }

}
