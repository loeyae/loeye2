<?php

/**
 * <{$className}>.php
 *
 * @author Zhang Yi <loeyae@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache License
 * @version <{$smarty.now|date_format:'%Y-%m-%d %H:%M:%S'}>
 */
namespace <{$namespace}>;

use loeye\base\Exception;
use loeye\client\Client;
use loeye\client\Request;
use loeye\client\Response;

/**
 * <{$className}>
 *
 *  @package <{$namespace}>
 */
class <{$className}> extends Client
{
    /**
     * property name
     */
    private $bundle = '<{$property}>';

    /**
     * @inheritDoc
     */
    public function __construct()
    {
        parent::__construct($this->bundle);
    }

<{$classBody}>

    /**
     * @inheritDoc
     */
    public function responseHandle($cmd, Response $resp)
    {
        $result = json_decode($resp->getContent(), true);
        $code = $result['status']['code'];
        $statusCode = $resp->getStatusCode();
        if ($statusCode === self::REQUEST_STATUS_OK && (int)$code === LOEYE_REST_STATUS_OK) {
            switch ($cmd) {
                default:
                    return $result;
            }
        } else {
            $req_url = $resp->getRequest()->getUri();
            if ($code !== LOEYE_REST_STATUS_OK) {
                $errmsg = $result['status']['message'];
                $msg = sprintf(
                    "[%s] request :%s \nhttp_code : %s\nmessage:%s",
                    self::class, $req_url, $statusCode, $errmsg
                );
                return new Exception($msg, $code);
            }

            $errcode = $resp->getErrorCode();
            $errmsg = $resp->getErrorMsg();
            $msg = sprintf(
                "[%s] request :%s \nhttp_code : %s\nmessage:%s",
                self::class, $req_url, $errcode, $errmsg
            );
            return new \Exception($msg, $errcode);
        }
    }
}
