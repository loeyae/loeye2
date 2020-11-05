<?php

/**
 * ValidateError.php
 *
 * PHP version 7
 *
 * Licensed under the Apache License, Version 2.0 (the "License"),
 * see LICENSE for more details: http://www.apache.org/licenses/LICENSE-2.0.
 *
 * @category PHP
 * @package  LOEYE
 * @author   Zhang Yi <loeyae@gmail.com>
 * @version  2019-8-4 16:07:55
 * @link     https://github.com/loeyae/loeye.git
 */

namespace loeye\error;

/**
 * ValidateError
 *
 * @author   Zhang Yi <loeyae@gmail.com>
 */
class ValidateError extends BusinessException
{

    public const DEFAULT_ERROR_MSG = 'validate has error';

    public const DEFAULT_ERROR_CODE = 510000;

    /**

     * @var array
     */
    protected $validateMessage;

    public function __construct(array $validateMessage, string $errorMessage = self::DEFAULT_ERROR_MSG, int
    $errorCode = self::DEFAULT_ERROR_CODE, $parameter = array())
    {
        parent::__construct($errorMessage, $errorCode, $parameter);
        $this->validateMessage = $validateMessage;
    }

    /**
     * getValidateMessage
     *
     * @return array|null
     */
    public function getValidateMessage(): ?array
    {
        return $this->validateMessage;
    }


}
