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
    protected $startTime;

    /**
     * startQuery
     *
     * @param string $sql
     * @param array|null $params
     * @param array|null $types
     *
     * @return void
     */
    public function startQuery($sql, ?array $params = null, ?array $types = null): void
    {
        $this->startTime = microtime(true);
        $message         = ['Start Query @' . date('Y-m-d H:i:s u')];
        $message[]         = 'sql: ' . $sql;
        $message[]         = 'params: ' . print_r($params, true);
        $message[]         = 'types: ' . print_r($types, true);
        \loeye\base\Logger::log($message, \loeye\base\Logger::LOEYE_LOGGER_TYPE_DEBUG);
    }

    /**
     * stopQuery
     *
     * @return void
     */
    public function stopQuery(): void
    {
        $endTime = microtime(true);
        $message = ['End Query @' . date('Y-m-d H:i:s u')];
        $message[] = 'time over: ' . ($endTime - $this->startTime);
        \loeye\base\Logger::log($message, \loeye\base\Logger::LOEYE_LOGGER_TYPE_DEBUG);
    }

}
