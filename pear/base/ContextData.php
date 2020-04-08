<?php

/**
 * ContextData.php
 *
 * PHP version 7
 *
 * Licensed under the Apache License, Version 2.0 (the "License"),
 * see LICENSE for more details: http://www.apache.org/licenses/LICENSE-2.0.
 *
 * @category PHP
 * @package  LOEYE
 * @author   Zhang Yi <loeyae@gmail.com>
 * @version  2018-07-23 22:44:28
 * @link     https://github.com/loeyae/loeye2.git
 */

namespace loeye\base;

use self;

/**
 * ContextData
 *
 * @author   Zhang Yi <loeyae@gmail.com>
 */
class ContextData
{

    /**
     * data
     *
     * @var mixed
     */
    public $data;

    /**
     * allow access times
     *
     * @var int
     */
    protected $allowAccessTimes = 0;

    /**
     * accessed times
     *
     * @var int
     */
    protected $accessedTimes = 0;

    /**
     * __construct
     *
     * @param mixed $data      data
     * @param int   $expire    expire times
     */
    public function __construct($data, $expire = 1)
    {
        $this->data = $data;
        $this->expire($expire);
    }

    /**
     * init
     *
     * @param mixed $data      data
     * @param int   $expire    expire times
     *
     * @return self
     */
    public static function init($data, $expire = 1): ContextData
    {
        return new self($data, $expire);
    }

    /**
     * getData
     *
     * @return mixed
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * __toString
     *
     * @return string
     */
    public function __toString()
    {
        return (string)var_export($this->data, true);
    }

    /**
     * __invoke
     *
     * @param bool $trace trace info
     * @return mixed
     */
    public function __invoke($trace = false)
    {
        if (false === $trace) {
            ++$this->accessedTimes;
        }
        return $this->data;
    }

    /**
     * isEmpty
     *
     * @param bool $ignore ignore 0
     *
     * @return boolean
     */
    public function isEmpty($ignore = true): bool
    {
        if ($ignore) {
            return empty($this->data) && !is_numeric($this->data);
        }
        return empty($this->data);
    }

    /**
     * isExpire
     *
     * @return boolean
     */
    public function isExpire(): bool
    {
        if (0 === $this->allowAccessTimes || 0 === $this->accessedTimes):
            return false;
        endif;
        return $this->allowAccessTimes <= $this->accessedTimes;
    }

    /**
     * expire
     *
     * @param int $expire    expire
     *
     * @return void
     */
    public function expire($expire = 1): void
    {
        if (null === $expire) {
            $expire = 1;
        }
        $this->allowAccessTimes = $expire;
        $this->accessedTimes = 0;
    }

    /**
     * getExpire
     *
     * @return mixed
     */
    public function getExpire()
    {
        return $this->allowAccessTimes;
    }

}
