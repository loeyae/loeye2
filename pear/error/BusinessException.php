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
    const DEFAULT_ERROR_CODE = 400000;

    /**
     * default error message
     */
    const DEFAULT_ERROR_MSG = "Business Error";

}
