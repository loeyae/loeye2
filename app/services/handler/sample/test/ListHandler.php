<?php

/**
 * ListHandler.php
 *
 * Licensed under the Apache License, Version 2.0 (the "License"),
 * see LICENSE for more details: http://www.apache.org/licenses/LICENSE-2.0.
 *
 * @author  Zhang Yi <loeyae@gmail.com>
 * @version 2019-02-25 16:16:12
 */
namespace app\services\handler\sample\test;
/**
 * ListHandler
 *
 * @author   Zhang Yi <loeyae@gmail.com>
 */
class ListHandler extends \app\services\handler\AbstractHandler
{

    /**
     * @var bool is restrict access
     */
    protected $access = true;

    /**
     * @var bool is oauth
     */
    protected $oauth = true;

    /**
     * @var string method
     */
    protected $method = 'GET';

    /**
     * @var object server
     */
    protected $server;

    /**
     * initServer
     *
     * @return Server
     */
    protected function initServer()
    {
        $this->server = new \app\services\server\SampleServer($this->context->getAppConfig());
    }

    /**
     * operate
     *
     * @param array $req req data
     *
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function operate($req)
    {
        $id = isset($this->pathParameter[4]) ? $this->pathParameter[4] : 0;
        return $this->server->listUser($id);
    }

}
