<?php

/**
 * TokenServer.php
 *
 * Licensed under the Apache License, Version 2.0 (the "License"),
 * see LICENSE for more details: http://www.apache.org/licenses/LICENSE-2.0.
 *
 * @author  Zhang Yi <loeyae@gmail.com>
 * @version SVN: $Id: Zhang Yi $
 */
namespace app\services\server;
/**
 * TokenServer
 *
 * @author   Zhang Yi <loeyae@gmail.com>
 */
class TokenServer extends AbstractServer
{

    const TOKEN_EXPIRE_TIME = 7200;

    /**
     * @var \loeye\base\DB db
     */
    protected $db;

    /**
     * @var string entity class name
     */
    protected $entity;

    /**
     * init
     *
     * @return void
     */
    protected function init()
    {
        $this->db = \loeye\base\DB::getInstance($this->config);
        $this->entity = \app\models\entity\Token::class;
    }

    /**
     * getCertif
     *
     * @param string $appid appid
     *
     * @return \app\models\entity\Token|null
     */
    public function getCertif($appid)
    {
        return $this->db->entity($this->entity, $appid);
    }

    /**
     * encode token
     *
     * @param string $appid  app id
     * @param string $secret app secret
     *
     * @return string
     * @throws \Exception
     */
    public function encodeToken($appid, $secret = null)
    {
        $data = $this->getCertif($appid);
        if (empty($data)) {
            throw new \loeye\base\Exception('Invalid app key', 40001);
        }
        if ($secret != null && $data->getSecret() != $secret) {
            throw new \loeye\base\Exception('Invalid app secret ', 40001);
        }
        if ($secret == null) {
            $secret = $data->getSecret();
        }
        if ($secret == null) {
            throw new \loeye\base\Exception('Invalid app secret ', 40002);
        }
        $refreshTime = time();
        if ($data->getToken() && $refreshTime - $data->getRefreshTime() < self::TOKEN_EXPIRE_TIME)
        {
            return $data->getToken();
        }
        $key = floor($refreshTime / self::TOKEN_EXPIRE_TIME);
        $sourceStr = http_build_query(['appid' => $appid, 'secret' => md5($secret), 'time' => $refreshTime]);
        $accessToken = \loeye\lib\Secure::crypt((string )$key, $sourceStr);
        if ($accessToken) {
            $data->setRefreshTime($refreshTime);
            $data->setToken($accessToken);
            $data->setExpireIn(self::TOKEN_EXPIRE_TIME);
            $this->db->save($entity);
        }
        return $accessToken;
    }

    /**
     * decode token
     *
     * @param string $accessToken accessToken
     *
     * @return boolean|array
     */
    public function decodeToken($accessToken)
    {
        $accessToken = rawurldecode($accessToken);
        $key = floor(time() / self::TOKEN_EXPIRE_TIME);
        $times = 0;
        $sourceData = [];
        while (empty($sourceData)) {
            $sourceStr = \loeye\lib\Secure::crypt((string )$key, $accessToken, true);
            if ($sourceStr) {
                parse_str($sourceStr, $sourceData);
            }
            if (!empty($sourceData)) {
                break;
            }
            $key--;
            $times++;
            if ($times >= 2) {
                return false;
            }
        }
        if (empty($sourceData['appid']) || empty($sourceData['secret']) || empty($sourceData['time']) || floor($sourceData['time'] / self::TOKEN_EXPIRE_TIME) != $key) {
            return null;
        }
        return $sourceData;
    }


}
