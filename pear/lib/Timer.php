<?php

/**
 * Timer.php
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

namespace loeye\lib;

use loeye\base\Exception;

/**
 * Timer
 *
 * @author   Zhang Yi <loeyae@gmail.com>
 */
class Timer
{

    /**
     * @var float start time
     */
    private $startTime;

    /**
     * @var float end time
     */
    private $endTime;

    /**
     * Start timer
     *
     * @return void
     */
    public function start(): void
    {
        $this->startTime = microtime(true);
    }

    /**
     * Stop timer
     *
     * @return void
     */
    public function stop(): void
    {
        $this->endTime = microtime(true);
    }

    /**
     * getStart
     *
     * @return float
     */
    public function getStart(): float
    {
        return $this->startTime;
    }

    /**
     * getEnd
     *
     * @return float
     */
    public function getEnd(): float
    {
        return $this->endTime;
    }

    /**
     * getDuration
     *
     * @return float second
     * @throws Exception
     */
    public function getDuration(): float
    {
        if ($this->startTime === null) {
            throw new Exception('timer is not started');
        }
        if ($this->endTime === null) {
            throw new Exception('timer is not stop');
        }
        return number_format(($this->endTime - $this->startTime) * 1000, 4, '.', '') . ' ms';
    }

}
