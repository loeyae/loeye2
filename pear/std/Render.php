<?php

/**
 * Render.php
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

namespace loeye\std;

/**
 * interface Render
 *
 * @author   Zhang Yi <loeyae@gmail.com>
 */
interface Render
{

    /**
     * header
     *
     * @param Response $response response
     *
     * @return void
     */
    public function header(Response $response): void ;

    /**
     * output
     *
     * @param Response $response response
     *
     * @return void
     */
    public function output(Response $response): void ;
}
