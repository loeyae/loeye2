<?php

/**
 * Resource.php
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

namespace loeye\service;

/**
 * Resource
 *
 * @author   Zhang Yi <loeyae@gmail.com>
 */
abstract class Resource implements \loeye\std\Handler
{
    use \loeye\std\ConfigTrait;

    const BUNDLE = 'service';

    /**
     *
     * @var \loeye\base\Context
     */
    protected $context;

    public function __construct(\loeye\base\Context $context)
    {
        $this->context = $context;
    }

    /**
     * get
     *
     * @param \loeye\service\Request  $req
     * @param \loeye\service\Response $resp
     *
     * @return void
     */
    abstract protected function get(Request $req, Response $resp);

    /**
     * post
     *
     * @param \loeye\service\Request  $req
     * @param \loeye\service\Response $resp
     *
     * @return void
     */
    abstract protected function post(Request $req, Response $resp);

    /**
     * delete
     *
     * @param \loeye\service\Request  $req
     * @param \loeye\service\Response $resp
     *
     * @return void
     */
    abstract protected function put(Request $req, Response $resp);

    /**
     * post
     *
     * @param \loeye\service\Request  $req
     * @param \loeye\service\Response $resp
     *
     * @return void
     */
    abstract protected function delete(Request $req, Response $resp);

    /**
     * handle
     *
     * @return void
     */
    public function handle()
    {
        $response = $this->context->getResponse();
        $request  = $this->context->getRequest();
        $method   = strtolower($request->getMethod());
        $format   = $request->getFormatType();
        if (empty($format)) {
            $response->setFormat('json');
        }
        if (method_exists($this, $method)) {
            $this->$method($request, $response);
        } else {
            $response->setStatusCode(LOEYE_REST_STATUS_METHOD_NOT_FOUND);
            $response->setStatusMessage('Request method not found');
        }
    }

}
