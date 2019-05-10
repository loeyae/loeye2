<?php

/**
 * SegmentRender.php
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

/**
 * Description of SegmentRender
 *
 * @author   Zhang Yi <loeyae@gmail.com>
 */
class SegmentRender implements \loeye\std\Render
{

    /**
     * header
     *
     * @param \loeye\std\Response $response respnse
     *
     * @return void
     */
    public function header(\loeye\std\Response $response)
    {
        $headers = $response->getHeaders();
        if (!array_key_exists('Content-Type', $headers) && !array_key_exists('Content-type', $headers) && !array_key_exists('content-type', $headers)) {
            $response->addHeader('Content-Type', 'text/html; charset=UTF-8');
        }
        $response->setHeaders();
    }

    /**
     * output
     *
     * @param \loeye\std\Response $response response
     *
     * @return void
     */
    public function output(\loeye\std\Response $response)
    {
        $output = $response->getOutput();
        foreach ($output as $sagment) {
            $this->fprint($sagment);
        }
    }

    /**
     * fprint
     *
     * @param string $item item
     *
     * @reutn void;
     */
    protected function fprint($item)
    {
        if (is_array($item)) {
            foreach ($item as $value) {
                $this->fprint($value . PHP_EOL);
            }
        } else {
            echo $item;
        }
    }

}
