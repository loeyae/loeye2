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
     * expire time
     *
     * @var int
     */
    protected $expire;

    /**
     * __construct
     *
     * @param mixed $data      data
     * @param int   $expire    expire times
     * @param int   $timestamp timestamp
     */
    public function __construct($data, $expire = null, $timestamp = null)
    {
        $this->data = $data;
        $this->expire($expire, $timestamp);
    }

    /**
     * init
     *
     * @param mixed $data      data
     * @param int   $expire    expire times
     * @param int   $timestamp timestamp
     *
     * @return \self
     */
    static public function init($data, $expire = null, $timestamp = null)
    {
        return new self($data, $expire, $timestamp);
    }

    /**
     * __toString
     *
     * @return string
     */
    public function __toString()
    {
        return var_export($this->data, true);
    }

    /**
     * __invoke
     *
     * @return mixed
     */
    public function __invoke()
    {
        return $this->data;
    }

    /**
     * isEmpyt
     *
     * @param bool $ignore ignore 0
     *
     * @return boolean
     */
    public function isEmpyt($ignore = true)
    {
        if ($ignore) {
            return empty($this->data) && !is_numeric($this->data);
        }
        return empty($this->data);
    }

    /**
     * isExpire
     *
     * @param int $timestamp timestamp
     *
     * @return boolean
     */
    public function isExpire($timestamp = null)
    {
        if (isset($this->expire) && empty($this->expire)):
            return false;
        elseif (isset($this->expire)):
            if ($timestamp == null):
                $timestamp = time();
            endif;
            return $this->expire - $timestamp < 0;
        endif;
        return true;
    }

    /**
     * expire
     *
     * @param int $expire    expire
     * @param int $timestamp timestamp
     *
     * @return void
     */
    public function expire($expire = null, $timestamp = null)
    {
        if ($expire !== null) {
            if ($timestamp == null) {
                $timestamp = time();
            }
            $this->expire = $timestamp + intval($expire);
        }
    }

    /**
     * getExpire
     *
     * @return mixed
     */
    public function getExpire()
    {
        return $this->expire;
    }

}
