<?php

/**
 * Response.php
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

namespace loeye\service;

/**
 * Response
 *
 * @author   Zhang Yi <loeyae@gmail.com>
 */
class Response extends \loeye\std\Response
{

    /**
     * setStatusMessage
     *
     * @param string $message message
     *
     * @return void
     */
    public function setStatusMessage($message): void
    {
        $this->statusText = $message;
    }

    /**
     * setContent
     *
     * @param mixed  $data        data
     * @param string $contentType content type
     *
     * @return void
     */
    public function setContent($data, $contentType = null): void
    {
        if (!empty($contentType)) {
            $this->headers->set('Content-Type', $contentType);
        }
        $this->output = $data;
    }


    /**
     * output
     *
     * @return mixed
     */
    public function getOutput()
    {
        return $this->output;
    }

}
