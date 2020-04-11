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
 * @version  2018-07-23 22:44:28
 * @link     https://github.com/loeyae/loeye2.git
 */

namespace loeye\socket;

use Channel\Server;
use Channel\Client;
use Closure;
use Exception;

/**
 * Worker
 *
 * @author   Zhang Yi <loeyae@gmail.com>
 */
abstract class Worker
{

    public const EVENT_BUFFER_FULL   = 'bufferFull';
    public const EVENT_BUFFER_DRAIN  = 'bufferDrain';
    public const EVENT_CONNECT       = 'connect';
    public const EVENT_CLOSE         = 'close';
    public const EVENT_ERROR         = 'error';
    public const EVENT_MESSAGE       = 'message';
    public const EVENT_WORKER_START  = 'workerStart';
    public const EVENT_WORKER_RELOAD = 'workerReload';
    public const EVENT_WORKER_STOP   = 'workerStop';
    /**
     * @var Closure
     */
    private $onBufferDrain;
    /**
     * @var Closure
     */
    private $onBufferFull;
    /**
     * @var Closure
     */
    private $onConnect;
    /**
     * @var Closure
     */
    private $onClose;
    /**
     * @var Closure
     */
    private $onError;
    /**
     * @var Closure
     */
    private $onMessage;
    /**
     * @var Closure
     */
    private $onWorkerStart;
    /**
     * @var Closure
     */
    private $onWorkerReload;
    /**
     * @var Closure
     */
    private $onWorkerStop;

    /**
     * buildWorker
     *
     * @param string $socket socket name
     * @param array $context context option
     * @param string $workerId worker id
     *
     * @return \Workerman\Worker
     */
    public static function buildWorker($socket, $context = [], $workerId = null): \Workerman\Worker
    {
        $worker = new \Workerman\Worker($socket, $context);
        if (null !== $workerId) {
            $worker->workerId = $workerId;
        }
        return $worker;
    }

    /**
     * buildChannelServer
     *
     * @param string $ip   ip address
     * @param int    $port port
     *
     * @return Server
     */
    public static function buildChannelServer($ip = '0.0.0.0', $port = 2206): Server
    {
        return new Server($ip, $port);
    }

    /**
     * getChannelClient
     *
     * @param string $ip ip address
     * @param int $port port
     *
     * @return void
     * @throws Exception
     */
    public static function connectChannelServer($ip = '127.0.0.1', $port = 2206): void
    {
        Client::connect($ip, $port);
    }

    /**
     * initWorker
     *
     * @return void
     */
    abstract public function initWorker(): void;

    /**
     * bind
     *
     * @param Worker $worker worker instance
     * @param string $event event name
     * <p>
     * bufferFull bufferDrain connect close error workerStart workerReload workerStop
     * </p>
     * @param callable $callback call back
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
    public static function bind(Worker $worker, $event, callable $callback): void
    {
        switch ($event) {
            case self::EVENT_BUFFER_DRAIN:
                $worker->onBufferDrain = static function ($connection) use ($callback, $worker) {
                    $callback($connection, $worker);
                };
                break;
            case self::EVENT_BUFFER_FULL:
                $worker->onBufferFull = static function ($connection) use ($callback, $worker) {
                    $callback($connection, $worker);
                };
                break;
            case self::EVENT_CONNECT:
                $worker->onConnect = static function ($connection) use ($callback, $worker) {
                    $callback($connection, $worker);
                };
                break;
            case self::EVENT_CLOSE:
                $worker->onClose = static function ($connection) use ($callback, $worker) {
                    $callback($connection, $worker);
                };
                break;
            case self::EVENT_ERROR:
                $worker->onError = static function ($connection, $code, $message) use ($callback, $worker) {
                    $callback($connection, $code, $message, $worker);
                };
                break;
            case self::EVENT_MESSAGE:
                $worker->onMessage = static function ($connection, $data) use ($callback, $worker) {
                    $callback($connection, $data, $worker);
                };
                break;
            case self::EVENT_WORKER_START:
                $worker->onWorkerStart = static function ($worker) use ($callback) {
                    $callback($worker);
                };
                break;
            case self::EVENT_WORKER_RELOAD:
                $worker->onWorkerReload = static function ($worker) use ($callback) {
                    $callback($worker);
                };
                break;
            case self::EVENT_WORKER_STOP:
                $worker->onWorkerStop = static function ($worker) use ($callback) {
                    $callback($worker);
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
    public function run(): void
    {
        $this->initWorker();
        \Workerman\Worker::runAll();
    }

}
