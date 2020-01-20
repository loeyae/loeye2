<?php

/**
 * CacheTrait.php
 *
 * PHP version 7
 *
 * Licensed under the Apache License, Version 2.0 (the "License"),
 * see LICENSE for more details: http://www.apache.org/licenses/LICENSE-2.0.
 *
 * @category PHP
 * @package  LOEYE
 * @author   Zhang Yi <loeyae@gmail.com>
 * @version  2020年1月20日 下午4:21:27
 * @link     https://github.com/loeyae/loeye.git
 */

namespace loeye\std;

/**
 * CacheTrait
 *
 * @author   Zhang Yi <loeyae@gmail.com>
 */
trait  CacheTrait
{

    public function getMeachedClient($setting)
    {
        $persistent_id = isset($setting['persistent_id']) ? $setting['persistent_id'] : PROJECT_NAMESPACE;
        $client        = new \Memcached($persistent_id);
        assert($setting['servers'], 'Invalid Memcached Server.');
        $client->addServers($setting['servers']);
        return $client;
    }

    public function getRedisClient($setting)
    {
        $client     = new \Redis();
        $persistent = isset($setting['persistent']) ? $setting['persistent'] : false;
        $host       = $setting['host'] ?? '127.0.0.1';
        $port       = $setting['port'] ?? 6379;
        $password   = $setting['password'] ?? null;
        $timeout    = $setting['timeout'] ?? 5;
        if ($persistent) {
            $client->pconnect($host, $port, $timeout);
        } else {
            $client->connect($host, $port, $timeout);
        }
        if ($password) {
            $client->auth($password);
        }
        return $client;
    }
}
