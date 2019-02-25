<?php

/**
 * AbstractHandler.php
 *
 * Licensed under the Apache License, Version 2.0 (the "License"),
 * see LICENSE for more details: http://www.apache.org/licenses/LICENSE-2.0.
 *
 * @author  Zhang Yi <loeyae@gmail.com>
 * @version SVN: $Id: Zhang Yi $
 */
namespace app\services\handler;

use loeye\service\Handler;

/**
 * AbstractHandler
 *
 * @author   Zhang Yi <loeyae@gmail.com>
 */
abstract class AbstractHandler extends Handler
{

    const ERR_TOKEN_EXPIRED   = 13;
    const ERR_TOKEN_INVALID   = 14;
    const SECRUITY_RULE_DENY  = 'deny';
    const SECRUITY_RULE_ALLOW = 'allow';

    static public $OPENER_ERR_MAPPING = [
        self::ERR_TOKEN_EXPIRED => LOEYE_REST_STATUS_DENIED,
        self::ERR_TOKEN_INVALID => LOEYE_REST_STATUS_DENIED,
    ];
    static public $OPENER_ERR_MSG = [
        self::ERR_TOKEN_EXPIRED => 'Token expired',
        self::ERR_TOKEN_INVALID => 'Invalid token',
    ];
    protected $securityBundle = 'security';
    protected $oauth = false;
    protected $access = false;

    /**
     * initServer
     *
     * @return void
     */
    abstract protected function initServer();

    /**
     * operate
     *
     * @param mixed $req request data
     *
     * @return mixed
     */
    abstract protected function operate($req);

    /**
     * checkIp
     *
     * @return true
     * @throws \loeye\base\Exception
     */
    protected function checkIp()
    {
        $setting = $this->getSecuritySetting();
        $ip      = $_SERVER['REMOTE_ADDR'];
        if ($ip == '::1') {
            $ip = '127.0.0.1';
        }
        $denyRule  = !empty($setting[self::SECRUITY_RULE_DENY]) ? (array) $setting[self::SECRUITY_RULE_DENY] : [];
        $allowRule = !empty($setting[self::SECRUITY_RULE_ALLOW]) ? (array) $setting[self::SECRUITY_RULE_ALLOW] : [];
        if ($denyRule) {
            foreach ($denyRule as $rule) {
                if ($this->inIpRule($ip, $rule)) {
                    throw new \loeye\base\Exception(parent::$ERR_MSG[parent::ERR_ACCESS_DENIED], parent::ERR_ACCESS_DENIED);
                }
            }
        }
        if ($allowRule) {
            foreach ($allowRule as $rule) {
                if ($this->inIpRule($ip, $rule)) {
                    return true;
                }
            }
            throw new \loeye\base\Exception(parent::$ERR_MSG[parent::ERR_ACCESS_DENIED], parent::ERR_ACCESS_DENIED);
        }
        return true;
    }

    /**
     * is ip in rule
     *
     * @param string $ip   ip string
     * @param string $rule rule string
     *
     * @return boolean
     */
    protected function inIpRule($ip, $rule)
    {
        if (is_string($rule)) {
            $mlinepos = mb_stripos($rule, '-');
            if ($mlinepos !== false) {
                $dotrpos = mb_strripos($rule, '.');
                if (mb_substr($rule, 0, $dotrpos) == mb_substr($ip, 0, $dotrpos)) {
                    $endrule = mb_substr($rule, $dotrpos + 1);
                    $endip = mb_substr($ip, $dotrpos + 1);
                    $dotpos = mb_stripos($endip, '.');
                    if ($dotpos !== false) {
                        $endip = mb_substr($endip, 0, $dotpos);
                    }
                    $endrulearr = explode('-', $endrule);
                    if (count($endrulearr) == 2 && intval($endip) >= intval($endrulearr[0]) && intval($endip) <= intval($endrulearr[1])) {
                        return true;
                    }
                }
            } else {
                $rule = rtrim($rule, '.*');
                if (mb_substr($ip, 0, mb_strlen($rule)) == $rule) {
                    return true;
                }
            }
        }
    }

    /**
     * getSecuritySetting
     *
     * @return array
     */
    protected function getSecuritySetting()
    {
        $moduleKey  = \loeye\base\UrlManager::REWRITE_KEY_PREFIX . \loeye\service\Dispatcher::KEY_MODULE;
        $serviceKey = \loeye\base\UrlManager::REWRITE_KEY_PREFIX . \loeye\service\Dispatcher::KEY_SERVICE;
        if (isset($_REQUEST[$moduleKey])) {
            $property = $_REQUEST[$moduleKey];
        } else {
            $property = $this->config->getPropertyName();
        }
        $config         = $this->bundleConfig($property, $this->securityBundle);
        $globalSetting  = $config->get('global');
        $currentSetting = [];
        if (isset($_REQUEST[$serviceKey])) {
            $currentSetting = $config->get($_REQUEST[$serviceKey]);
        }
        return !empty($currentSetting) ? $currentSetting : $globalSetting;
    }

    /**
     * checkMethod
     *
     * @return void
     * @throws \loeye\base\Exception
     */
    protected function checkMethod()
    {
        $method = $this->context->getRequest()->getMethod();
        if (strtolower($method) != strtolower($this->method)) {
            throw new \loeye\base\Exception(parent::$ERR_MSG[parent::ERR_NOT_ALLOWD_METHOD], parent::ERR_NOT_ALLOWD_METHOD);
        }
    }

    /**
     * oauth
     *
     * @throws \loeye\base\Exception
     */
    protected function oauth()
    {
        return true;
        $tokenServer = new \app\services\server\TokenServer($this->config);
        $accessToken = $this->queryParameter['access_token'];
        $sourceData  = $tokenServer->decodeToken($accessToken);
        if ($sourceData === false) {
            throw new \loeye\base\Exception(self::$OPENER_ERR_MSG[self::ERR_TOKEN_EXPIRED], self::$OPENER_ERR_MAPPING[self::ERR_TOKEN_EXPIRED]);
        }
        if (!$sourceData) {
            throw new \loeye\base\Exception(self::$OPENER_ERR_MSG[self::ERR_TOKEN_INVALID], self::$OPENER_ERR_MAPPING[self::ERR_TOKEN_INVALID]);
        }
        if (time() - $sourceData['time'] > self::TOKEN_EXPIRE_TIME) {
            throw new \loeye\base\Exception(self::$OPENER_ERR_MSG[self::ERR_TOKEN_EXPIRED], self::$OPENER_ERR_MAPPING[self::ERR_TOKEN_EXPIRED]);
        }
        $certif = $tokenServer->getCertif($sourceData['appid']);
        if (md5($certif->getSecret()) != $sourceData['secret'] || $certif->getRefreshTime() != $sourceData['time']) {
            throw new \loeye\base\Exception(self::$OPENER_ERR_MSG[self::ERR_TOKEN_INVALID], self::$OPENER_ERR_MAPPING[self::ERR_TOKEN_INVALID]);
        }
    }

    /**
     * process
     *
     * @param array $req request data
     *
     * @return mixed
     */
    protected function process($req)
    {
        if ($this->access) {
            $this->checkIp();
        }
        $this->checkMethod();
        if ($this->oauth) {
            $this->oauth();
        }
        $this->initServer();
        return $this->operate($req);
    }

}
