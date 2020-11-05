<?php

/**
 * ParallelClientMgr.php
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

namespace loeye\client;


use Throwable;
use function GuzzleHttp\Promise\unwrap;

/**
 * Description of ParallelClientManager
 *
 * @author   Zhang Yi <loeyae@gmail.com>
 */
class ParallelClientManager
{

    /**
     *
     * @var Client[] array
     */
    private $_parallelClient = [];

    /**
     * addClient
     *
     * @param Client $client client
     *
     * @return void
     */
    public function addClient(Client $client): void
    {
        $client->setParallel();
        $this->_parallelClient[] = $client;
    }

    /**
     * execute
     *
     * @return void
     * @throws Throwable
     */
    public function execute(): void
    {
        $promises = [];
        $idx = 0;
        foreach ($this->_parallelClient as $client) {
            $reqs = $client->getParallelRequest();
            foreach ($reqs as $req) {
                $promises[$idx] = $req->promise();
                $idx++;
            }
        }

        $results = unwrap($promises);

        $idx1 = 0;
        foreach ($this->_parallelClient as $client) {
            $reqs = $client->getParallelRequest();
            foreach ($reqs as $i => $req) {
                $req->setResponse($results[$idx1]);
                $client->onComplete($req, $i);
                $idx1++;
            }
        }
    }

    /**
     * reset
     *
     * @return void
     */
    public function reset(): void
    {
        foreach ($this->_parallelClient as $client) {
            $client->reset();
        }
        $this->_parallelClient = array();
    }

}
