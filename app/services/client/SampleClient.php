<?php

/**
 * SampleClient.php
 *
 * Licensed under the Apache License, Version 2.0 (the "License"),
 * see LICENSE for more details: http://www.apache.org/licenses/LICENSE-2.0.
 *
 * @author  Zhang Yi <loeyae@gmail.com>
 * @version 2019-02-25 17:39:00
 */

namespace app\services\client;

/**
 * SampleClient
 *
 * @author   Zhang Yi <loeyae@gmail.com>
 */
class SampleClient extends \loeye\client\Client
{

    /**
     * __construct
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct($bundle='smaple');
    }

    /**
     *
     * @param int   $id
     * @param mixed $ret
     *
     * @return mixed
     */
    public function getUser($id, &$ret = false)
    {
        $path = '/sample/test/get/';
        $path .= $id;
        $req = new \loeye\client\Request();
        $this->setReq($req, 'GET', $path);
        return $this->request(__FUNCTION__, $req, $ret);
    }

    public function listUser($id = 0, &$ret = false)
    {
        $path = '/sample/test/list';
        if ($id) {
            $path .= '/'. $id;
        }
        $req = new \loeye\client\Request();
        $this->setReq($req, 'GET', $path);
        return $this->request(__FUNCTION__, $req, $ret);
    }

    /**
     * responseHandle
     *
     * @param string                 $cmd  command
     * @param \loeye\client\Response $resp response
     *
     * @return mixed
     * @throws Exception
     */
    public function responseHandle($cmd, \loeye\client\Response $resp)
    {
        $result  = json_decode($resp->getContent(), true);
        $code = $result['status']['code'];
        if ((($statusCode = $resp->getStatusCode()) == static::REQUEST_STATUS_OK) && ($code == LOEYE_REST_STATUS_OK)
        ) {
            switch ($cmd) {
                default:
                    return $result;
            }
        } else {
            $req_url = $resp->getRequest()->getUri()->toString();
            if ($code != LOEYE_REST_STATUS_OK) {
                $errmsg = $result['status']['message'];
                $msg = sprintf(
                    "[SamplpleClient] request :%s \nhttp_code : %s\nmessage:%s",
                    $req_url, $statusCode, $errmsg
                );
                if (intval($code) == 0) {
                    $code = 500;
                }
                return new \loeye\base\Exception($msg, intval($code));
            } else {
                $errcode = $resp->getErrorCode();
                $errmsg = $resp->getErrorMsg();
                $msg = sprintf(
                    "[SampleClient] request :%s \nhttp_code : %s\nmessage:%s",
                    $req_url, $statusCode, $errmsg
                );
                if (intval($errcode) == 0) {
                    $errcode = 500;
                }
                return new \loeye\base\Exception($msg, intval($errcode));
            }
        }
    }

}
