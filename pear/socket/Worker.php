<?php

/**
 * Worker.php
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

namespace loeye\socket;

use \Workerman\Worker;
use \Workerman\Channel\Server;
use \Workerman\Channel\Client;

/**
 * Worker
 *
 * @author   Zhang Yi <loeyae@gmail.com>
 */
abstract class Worker
{

    const EVENT_BUFFER_FULL   = 'bufferFull';
    const EVENT_BUFFER_DRAIN  = 'bufferDrain';
    const EVENT_CONNECT       = 'connect';
    const EVENT_CLOSE         = 'close';
    const EVENT_ERROR         = 'error';
    const EVENT_MESSAGE       = 'message';
    const EVENT_WORKER_START  = 'workerStart';
    const EVENT_WORKER_RELOAD = 'workerReload';
    const EVENT_WORKER_STOP   = 'workerStop';

    /**
     * buildWorker
     *
     * @param string $socket   socket name
     * @param array  $context  context option
     * @param string $workerId worker id
     *
     * @return Worker
     */
    public static function buildWorker($socket, $context = [], $workerId = null)
    {
        return new Worker($socket, $context, $workerId);
    }

    /**
     * buildChannelServer
     *
     * @param string $ip   ip address
     * @param int    $port port
     *
     * @return Server
     */
    public static function buildChannelServer($ip = '0.0.0.0', $port = 2206)
    {
        return new Server($ip, $port);
    }

    /**
     * getChannelClient
     *
     * @param string $ip   ip address
     * @param int    $port port
     *
     * @return Client
     */
    public static function connectChannelServer($ip = '127.0.0.1', $port = 2206)
    {
        return Client::connect($ip, $port);
    }

    /**
     * initWorker
     *
     * @return void
     */
    abstract public function initWorker();

    /**
     * bind
     *
     * @param \Workerman\Worker $worker   worker instance
     * @param string            $event    event name
     * <p>
     * bufferFull bufferDrain connect close error workerStart workerReload workerStop
     * </p>
     * @param callable          $callback call back
     * <p>
     * bufferFull   => function (\Workerman\Connection\ConnectionInterface $conn,
     * \Workerman\Worker $worker) {} <br />
     * bufferDrain  => function (\Workerman\Connection\ConnectionInterface $conn,
     * \Workerman\Worker $worker) {} <br />
     * connect      => function (\Workerman\Connection\ConnectionInterface $conn,
     * \Workerman\Worker $worker) {} <br />
     * close        => function (\Workerman\Connection\ConnectionInterface $conn,
     * \Workerman\Worker $worker) {} <br />
     * error        => function (\Workerman\Connection\ConnectionInterface $conn,
     * $code, $msg, \Workerman\Worker $worker) {} <br />
     * message      => function (\Workerman\Connection\ConnectionInterface $conn,
     * $data, \Workerman\Worker $worker) {} <br />
     * workerStart  => function (\Workerman\Worker $worker) {} <br />
     * workerReload => function (\Workerman\Worker $worker) {} <br />
     * workerStop   => function (\Workerman\Worker $worker) {} <br />
     * </p>
     *
     * @return void
     */
    public static function bind(Worker $worker, $event, callable $callback)
    {
        switch ($event) {
            case self::EVENT_BUFFER_DRAIN:
                $worker->onBufferDrain = function ($connection) use ($callback, $worker) {
                    call_user_func($callback, $connection, $worker);
                };
                break;
            case self::EVENT_BUFFER_FULL:
                $worker->onBufferFull = function ($connection) use ($callback, $worker) {
                    call_user_func($callback, $connection, $worker);
                };
            case self::EVENT_CONNECT:
                $worker->onConnect = function ($connection) use ($callback, $worker) {
                    call_user_func($callback, $connection, $worker);
                };
                break;
            case self::EVENT_CLOSE:
                $worker->onClose = function ($connection) use ($callback, $worker) {
                    call_user_func($callback, $connection, $worker);
                };
                break;
            case self::EVENT_ERROR:
                $worker->onError = function ($connection, $code, $message) use ($callback, $worker) {
                    call_user_func($callback, $connection, $code, $message, $worker);
                };
                break;
            case self::EVENT_MESSAGE:
                $worker->onMessage = function ($connection, $data) use ($callback, $worker) {
                    call_user_func($callback, $connection, $data, $worker);
                };
                break;
            case self::EVENT_WORKER_START:
                $worker->onWorkerStart = function ($worker) use ($callback) {
                    call_user_func($callback, $worker);
                };
                break;
            case self::EVENT_WORKER_RELOAD:
                $worker->onWorkerReload = function ($worker) use ($callback) {
                    call_user_func($callback, $worker);
                };
                break;
            case self::EVENT_WORKER_STOP:
                $worker->onWorkerStop = function ($worker) use ($callback) {
                    call_user_func($callback, $worker);
                };
                break;
            default:
                break;
        }
    }

    /**
     * run
     *
     * @return void
     */
    public function run()
    {
        $this->initWorker();
        Worker::runAll();
    }

}
