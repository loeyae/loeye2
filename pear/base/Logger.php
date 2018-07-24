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
 * @version  GIT: $Id: Zhang Yi $
 * @link     https://github.com/loeyae/loeye.git
 */

namespace loeye\base;

use Monolog;

/**
 * Logger
 *
 * @author   Zhang Yi <loeyae@gmail.com>
 */
class Logger
{

    const LOEYE_LOGGER_TYPE_CRITICAL      = Monolog\Logger::CRITICAL;
    const LOEYE_LOGGER_TYPE_ERROR         = Monolog\Logger::ERROR;
    const LOEYE_LOGGER_TYPE_WARNING       = Monolog\Logger::WARNING;
    const LOEYE_LOGGER_TYPE_NOTICE        = Monolog\Logger::NOTICE;
    const LOEYE_LOGGER_TYPE_INFO          = Monolog\Logger::INFO;
    const LOEYE_LOGGER_TYPE_DEBUG         = Monolog\Logger::DEBUG;
    const LOEYE_LOGGER_TYPE_CONTEXT_TRACE = 50;

    private static $logger = [];

    /**
     * getLogger
     *
     * @param string $name logger name
     * @param string $file log file
     *
     * @return \Monolog\Logger
     */
    private static function getLogger($name, $file = null)
    {
        $logfile = $file ?? RUNTIME_LOG_DIR . DIRECTORY_SEPARATOR
                . PROJECT_NAMESPACE . DIRECTORY_SEPARATOR
                . 'error.log';
        $key     = md5($logfile);
        if (!isset(self::$logger[$key])) {
            $dateFormat         = "Y-m-d H:i:s u";
            $output             = "[%datetime%][%level_name%]%channel%: %message% %context% %extra%\n";
            $formatter          = new Monolog\Formatter\LineFormatter($output, $dateFormat);
            $handler            = new \Monolog\Handler\StreamHandler($logfile, RUNTIME_LOGGER_LEVEL);
            $handler->setFormatter($formatter);
            $logger             = new Monolog\Logger($name);
            $logger->setTimezone(new \DateTimeZone('Asia/Shanghai'));
            $logger->pushHandler($handler);
            self::$logger[$key] = $logger;
        }
        return self::$logger[$key];
    }

    /**
     * handle
     *
     * @param int    $no      no
     * @param string $message message
     * @param string $file    file
     * @param int    $line    line
     *
     * @return void
     */
    static public function handle($no, $message, $file, $line)
    {
        switch ($no) {
            case E_ERROR:
                $message = '[core] ' . $message;
                $type    = self::LOEYE_LOGGER_TYPE_ERROR;
                break;
            case E_USER_ERROR:
                $message = '[user] ' . $message;
                $type    = self::LOEYE_LOGGER_TYPE_ERROR;
                break;
            case E_WARNING:
                $message = '[core] ' . $message;
                $type    = self::LOEYE_LOGGER_TYPE_WARNING;
                break;
            case E_USER_WARNING:
                $message = '[user] ' . $message;
                $type    = self::LOEYE_LOGGER_TYPE_WARNING;
                break;
            case E_NOTICE:
                $message = '[core] ' . $message;
                $type    = self::LOEYE_LOGGER_TYPE_NOTICE;
                break;
            case E_USER_NOTICE:
                $message = '[user] ' . $message;
                $type    = self::LOEYE_LOGGER_TYPE_NOTICE;
                break;
            default:
                $message = '[other] ' . $message;
                $type    = self::LOEYE_LOGGER_TYPE_ERROR;
                break;
        }
        $log = $message . ' (' . $file . ':' . $line . ')' . PHP_EOL . 'Stack trace:' . PHP_EOL;
        $log .= self::getTraceInfo();
        self::log($log, $type);
    }

    /**
     * trigger
     *
     * @param string $message message
     * @param string $file    file
     * @param string $line    line
     * @param int    $type    logger type
     *
     * @return void
     */
    static public function trigger(
            $message, $file, $line, $type = Logger::LOEYE_LOGGER_TYPE_WARNING
    )
    {
        $log = $message . ' (' . $file . ':' . $line . ')' . PHP_EOL;
        self::log($log, $type);
    }

    /**
     * trace
     *
     * @param string $message message
     * @param int    $code    code
     * @param string $file    file
     * @param string $line    line
     *
     * @return void
     */
    static public function trace($message, $code, $file, $line, $type = self::LOEYE_LOGGER_TYPE_DEBUG)
    {
        $log = $message . ' error code ' . $code;
        $log .= ' (' . $file . ':' . $line . ')' . PHP_EOL . 'Stack trace:' . PHP_EOL;
        $log .= self::getTraceInfo();
        self::log($log, $type);
    }

    /**
     * log
     *
     * @param string $message message
     * @param int    $type    message type
     * @param string $file    file
     *
     * @return void
     */
    static public function log($message, $type = self::LOEYE_LOGGER_TYPE_ERROR, $file = null)
    {
        $logger = self::getLogger(PROJECT_NAMESPACE, $file);
        $logger->log($type, $message);
    }

    /**
     * getTraceInfo
     *
     * @return string
     */
    static public function getTraceInfo()
    {
        $message = '';
        $trace   = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
        foreach ($trace as $i => $t) {
            if (!isset($t['file'])) {
                $t['file'] = 'unknown';
            }
            if (!isset($t['line'])) {
                $t['line'] = 0;
            }
            if (!isset($t['function'])) {
                $t['function'] = 'unknown';
            }
            $message .= "#$i {$t['file']}({$t['line']}): ";
            if (isset($t['class'])) {
                $message .= $t['class'] . '->';
            }
            $message .= "{$t['function']}()" . '\r\n';
        }
        if (filter_has_var(INPUT_SERVER, 'REQUEST_URI')) {
            $message .= "# REQUEST_URI: " . filter_input(INPUT_SERVER, 'REQUEST_URI') . '\r\n';
        }
        return $message;
    }

}