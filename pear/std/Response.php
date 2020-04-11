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
 * @version  2018-07-23 22:44:28
 * @link     https://github.com/loeyae/loeye2.git
 */

namespace loeye\std;

use ArrayAccess;

/**
 * interface Response
 *
 * @author   Zhang Yi <loeyae@gmail.com>
 */
abstract class Response implements ArrayAccess
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
    public function offsetExists($offset): bool
    {
        return true;
    }

    /**
     * offsetGet
     *
     * @param mixed $offset offset
     *
     * @return mixed
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
     * @return mixed|void
     */
    public function offsetSet($offset, $value)
    {
    }

    /**
     * offsetUnset
     *
     * @param mixed $offset offset
     *
     * @return mixed|void
     */
    public function offsetUnset($offset)
    {
    }

    /**
     * addHeader
     *
     * @param string $name  header name
     * @param string $value header value
     *
     * @return void
     */
    public function addHeader($name, $value): void
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
    public function addOutput($data, $key = null): void
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
    public function setFormat($format): void
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

    /**
     * getHeader
     *
     * @return array
     */
    public function getHeaders(): array
    {
        return $this->header;
    }


    /**
     * setHeaders
     *
     * @return void
*/
    public function setHeaders(): void
    {
        foreach ($this->header as $key => $value) {
            if (is_numeric($key)) {
                header($value);
            } else {
                header("$key:$value");
            }
        }
    }

    abstract public function getOutput();
}
