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
 * @version  GIT: $Id: Zhang Yi $
 * @link     https://github.com/loeyae/loeye.git
 */

namespace loeye\client;


/**
 * Description of ParallelClientManager
 *
 * @author   Zhang Yi <loeyae@gmail.com>
 */
class ParallelClientManager
{

    /**
     *
     * @var \loeye\client\Client array
     */
    private $_parallelClient = [];

    /**
     * addClient
     *
     * @param \loeye\client\Client $client client
     *
     * @return void
     */
    public function addClient(\loeye\client\Client $client)
    {
        $client->setParallel();
        $this->_parallelClient[] = $client;
    }

    /**
     * excute
     *
     * @return void
     */
    public function excute()
    {
        $promises = [];
        $idx      = 0;
        foreach ($this->_parallelClient as $k => $client) {
            $reqs = $client->getParallelRequest();
            foreach ($reqs as $req) {
                $promises[$idx] = $req->promise();
                $idx++;
            }
        }

        $results = \GuzzleHttp\Promise\unwrap($promises);

        $idx = 0;
        foreach ($this->_parallelClient as $client) {
            $reqs = $client->getParallelRequest();
            foreach ($reqs as $i => $req) {
                $req->setResponse($results[$idx]);
                $client->onComplete($req, $i);
                $idx++;
            }
        }
    }

    /**
     * reset
     *
     * @return void
     */
    public function reset()
    {
        foreach ($this->_parallelClient as $client) {
            $client->reset();
        }
        $this->_parallelClient = array();
    }

}
