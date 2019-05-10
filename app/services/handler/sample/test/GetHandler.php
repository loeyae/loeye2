<?php

/**
 * GetHandler.php
 *
 * Licensed under the Apache License, Version 2.0 (the "License"),
 * see LICENSE for more details: http://www.apache.org/licenses/LICENSE-2.0.
 *
 * @author  Zhang Yi <loeyae@gmail.com>
 * @version 2019-02-25 14:54:58
 */
namespace app\services\handler\sample\test;

use \loeye\error\DataException;

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
        if (empty($user)) {
            throw new DataException(DataException::DATA_NOT_FOUND_ERROR_MSG, DataException::DATA_NOT_FOUND_ERROR_CODE);
        }
        return ['id' => $user->getId(), 'name' => $user->getName()];
    }

}
