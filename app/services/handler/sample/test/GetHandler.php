<?php

/**
 * GetHandler.php
 *
 * Licensed under the Apache License, Version 2.0 (the "License"),
 * see LICENSE for more details: http://www.apache.org/licenses/LICENSE-2.0.
 *
 * @author  Zhang Yi <loeyae@gmail.com>
 * @version SVN: $Id: Zhang Yi $
 */
namespace app\services\handler\sample\test;
/**
 * GetHandler
 *
 * @author   Zhang Yi <loeyae@gmail.com>
 */
class GetHandler extends \app\services\handler\AbstractHandler
{

    protected $access = true;
    protected $oauth = true;
    protected $method = 'GET';
    protected $server;

    /**
     * initServer
     *
     * @return void
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
        $id = $this->pathParameter[4];
        $user = $this->server->getUser($id);
        return ['id' => $user->getId(), 'name' => $user->getName()];
    }

}
