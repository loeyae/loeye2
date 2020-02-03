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
/**
 * DataException
 *
 * @author   Zhang Yi <loeyae@gmail.com>
 */
class DataException extends \loeye\base\Exception
{

    /**
     * default error code
     */
    const DEFAULT_ERROR_CODE = 420000;

    /**
     * default error message
     */
    const DEFAULT_ERROR_MSG = "Data Error";

    const DATA_NOT_FOUND_ERROR_CODE = 420404;
    const DATA_NOT_FOUND_ERROR_MSG = "Data Not Found";

    const DATA_NOT_EQUALS = 420001;
    const DATA_NOT_EQUALS_MSG = "%expected% not equals %actual%";

    const CONTEXT_VALUE_NOT_EQUALS = 420002;
    const CONTEXT_VALUE_NOT_EQUALS_MSG = "%key% of context not equals %expected%";

    const ARRAY_VALUE_NOT_EQUALS = 420003;
    const ARRAY_VALUE_NOT_EQUALS_MSG = "%key% of %data% not equals %expected%";

    public function __construct(string $errorMessage = self::DEFAULT_ERROR_MSG, int $errorCode = self::DEFAULT_ERROR_CODE, $parameter = array())
    {
        parent::__construct($errorMessage, $errorCode, $parameter);
    }

}
