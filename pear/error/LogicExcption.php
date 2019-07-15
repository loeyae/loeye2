<?php

/**
 * ConfigExcption.php
 *
 * PHP version 7
 *
 * Licensed under the Apache License, Version 2.0 (the "License"),
 * see LICENSE for more details: http://www.apache.org/licenses/LICENSE-2.0.
 *
 * @category PHP
 * @package  LOEYE
 * @author   Zhang Yi <loeyae@gmail.com>
 * @version  2019-7-15 23:45:48
 * @link     https://github.com/loeyae/loeye.git
 */

namespace loeye\error;

/**
 * ConfigExcption
 *
 * @author   Zhang Yi <loeyae@gmail.com>
 */
class LogicExcption extends \loeye\base\Exception
{

    /**
     * default error code
     */
    const DEFAULT_ERROR_CODE = 900000;

    /**
     * default error message
     */
    const DEFAULT_ERROR_MSG = "Logic Error";

    const CONTEXT_KEY_NOT_FOUND  = 900101;
    const CONTEXT_VALUE_IS_EMPTY = 900102;

    const DATA_KEY_NOT_FOUND  = 900201;
    const DATA_VALUE_IS_EMPTY = 900202;
    const DATA_AT_LEAST_EXIST_ONE_KEY = 900203;

}
