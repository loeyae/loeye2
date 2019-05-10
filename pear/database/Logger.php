<?php

/**
 * Logger.php
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

namespace loeye\database;

use Doctrine\DBAL\Logging\SQLLogger as BaseLogger;

/**
 * Logger
 *
 * @author   Zhang Yi <loeyae@gmail.com>
 */
class Logger implements BaseLogger
{

    protected $logger;
    protected $starttime;

    public function startQuery($sql, ?array $params = null, ?array $types = null)
    {
        $this->starttime = microtime(true);
        $message         = 'Sart Query @' . date('Y-m-d H:i:s u') . '\r\n';
        $message         .= 'sql: ' . $sql . '\r\n';
        $message         .= 'params: ' . print_r($params, true) . '\r\n';
        $message         .= 'types: ' . print_r($types, true) . '\r\n';
        \loeye\base\Logger::log($message, \loeye\base\Logger::LOEYE_LOGGER_TYPE_DEBUG);
    }

    public function stopQuery()
    {
        $endtime = microtime(true);
        $message = 'End Query @' . date('Y-m-d H:i:s u') . '\r\n';
        $message .= 'time over: ' . ($endtime - $this->starttime);
        \loeye\base\Logger::log($message, \loeye\base\Logger::LOEYE_LOGGER_TYPE_DEBUG);
    }

}
