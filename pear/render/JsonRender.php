<?php

/**
 * JsonRender.php
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

namespace loeye\render;

use loeye\std\Render;
use loeye\std\Response;

/**
 * Description of JsonRender
 *
 * @author   Zhang Yi <loeyae@gmail.com>
 */
class JsonRender implements Render
{
    //put your code here

    /**
     * header
     *
     * @param Response $response response
     *
     * @return void
     */
    public function header(Response $response): void
    {
        $response->addHeader('Content-Type', 'application/json; charset=UTF-8');
        $response->setHeaders();
    }

    /**
     * output
     *
     * @param Response $response response
     *
     * @return void
     */
    public function output(Response $response): void
    {
        $output = $response->getOutput();

        $json = json_encode($output, true);

        echo $json;
    }

}
