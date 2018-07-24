<?php

/**
 * Response.php
 *
 * PHP version 7
 *
 * Licensed under the Apache License, Version 2.0 (the "License"),
 * see LICENSE for more details: http://www.apache.org/licenses/LICENSE-2.0.
 *
 * @category PHP
 * @package  LOEYE
 * @author   Zhang Yi <loeyae@gmail.com>
 * @version  GIT: $Id: Zhang Yi $
 * @link     https://github.com/loeyae/loeye.git
 */

namespace loeye\std;

/**
 * interface Response
 *
 * @author   Zhang Yi <loeyae@gmail.com>
 */
abstract class Response implements \ArrayAccess
{

    protected $header = array();
    protected $output = array();
    protected $format;

    /**
     * offsetExists
     *
     * @param mixed $offset offset
     *
     * @return boolean
     */
    public function offsetExists($offset)
    {
        return true;
    }

    /**
     * offsetGet
     *
     * @param mixed $offset offset
     *
     * @return void
     */
    public function offsetGet($offset)
    {
        return null;
    }

    /**
     * offsetSet
     *
     * @param mixed $offset offset
     * @param mixed $value  value
     *
     * @return void
     */
    public function offsetSet($offset, $value)
    {
        return;
    }

    /**
     * offsetUnset
     *
     * @param mixed $offset offset
     *
     * @return void
     */
    public function offsetUnset($offset)
    {
        return;
    }

    /**
     * addHeader
     *
     * @param string $name  header name
     * @param string $value header value
     *
     * @return void
     */
    public function addHeader($name, $value)
    {
        $this->header[$name] = $value;
    }

    /**
     * addOutput
     *
     * @param mixed  $data data
     * @param string $key  key
     *
     * @return void
     */
    public function addOutput($data, $key = null)
    {
        if ($key !== null) {
            $this->output[$key] = $data;
        } else {
            $this->output[] = $data;
        }
    }

    /**
     * setFormat
     *
     * @param string $format format
     *
     * @return void
     */
    public function setFormat($format)
    {
        $this->format = $format;
    }

    /**
     * getFormat
     *
     * @return mixed
     */
    public function getFormat()
    {
        return (!empty($this->format)) ? $this->format : null;
    }

    abstract public function setHeaders();

    abstract public function getOutput();
}
