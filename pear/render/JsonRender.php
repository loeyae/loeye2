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
 * @version  GIT: $Id: Zhang Yi $
 * @link     https://github.com/loeyae/loeye.git
 */

namespace loeye\render;

/**
 * Description of JsonRender
 *
 * @author   Zhang Yi <loeyae@gmail.com>
 */
class JsonRender implements \loeye\std\Render
{
    //put your code here

    /**
     * header
     *
     * @param \loeye\std\Response $response response
     *
     * @return void
     */
    public function header(\loeye\std\Response $response)
    {
        $response->addHeader('Content-Type', 'application/json; charset=UTF-8');
        $response->setHeaders();
    }

    /**
     * output
     *
     * @param \loeye\std\Response $reponse response
     *
     * @return void
     */
    public function output(\loeye\std\Response $reponse)
    {
        $output = $reponse->getOutput();

        $json = json_encode($output, true);

        echo $json;
    }

}
