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

use loeye\base\Factory;

/**
 * interface Response
 *
 * @author   Zhang Yi <loeyae@gmail.com>
 */
abstract class Response extends \Symfony\Component\HttpFoundation\Response
{
    protected $output = array();
    protected $format;

    /**
     * __construct
     *
     * @param Request|null $req
     * @param string $content
     * @param int $status
     * @param array $headers
     */
    public function __construct(Request $req = null, $content = '', int $status = 200, array $headers = [])
    {
        parent::__construct($content, $status, $headers);
        $this->prepare($req);
    }

    /**
     * create
     *
     * @param Request|null $req
     * @param string $content
     * @param int $status
     * @param array $headers
     * @return Response
     */
    public static function create(Request $req = null, $content = '', $status = 200, $headers = [])
    {
        if (!$req) {
            $req = Factory::request();
        }
        return new static($req, $content, $status, $headers);
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
        $this->headers->set($name, $value);
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
        return !empty($this->format) ? $this->format : null;
    }

    /**
     * getHeader
     *
     * @return array
     */
    public function getHeaders(): array
    {
        return $this->headers->all();
    }


    /**
     * setHeaders
     *
     * @return void
*/
    public function setHeaders(): void
    {
        $this->sendHeaders();
    }

    /**
     * setStatusCode
     *
     * @param int $statusCode
     */
    public function setStatusCode(int $statusCode)
    {
        $this->statusCode = $statusCode;
    }


    abstract public function getOutput();
}
