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

    private $_serverProtocol;
    private $_statusCode;
    private $_statusMessage;
    private $_contentType;

    /**
     * __construct
     *
     * @param \loeye\service\Request $req request
     *
     * @return void
     */
    public function __construct(Request $req)
    {
        $this->_serverProtocol = $req->getServerProtocol();
        $this->_statusCode     = LOEYE_REST_STATUS_OK;
        $this->_statusMessage  = 'OK';
        $this->_contentType    = 'text/plain; charset=utf-8';
        $this->_responseData   = '';
        $this->header = [];
    }

    /**
     * setStatusCode
     *
     * @param int $code status code
     *
     * @return void
     */
    public function setStatusCode($code)
    {
        $this->_statusCode = $code;
    }

    /**
     * setStatusMessage
     *
     * @param string $message message
     *
     * @return void
     */
    public function setStatusMessage($message)
    {
        $this->_statusMessage = $message;
    }

    /**
     * setContent
     *
     * @param mixed  $data        data
     * @param string $contentType content type
     *
     * @return void
     */
    public function setContent($data, $contentType = null)
    {
        if (!empty($contentType)) {
            $this->_contentType = $contentType;
        }
        $this->output = $data;
    }

    public function setHeaders()
    {
        $header = $this->_serverProtocol . ' ' . $this->_statusCode . ' ' . $this->_statusMessage;
        header($header);
        parent::setHeaders();
        if (!in_array('Content-Type', $this->header)) {
            header('Content-Type:'. $this->_contentType);
        }
    }

    /**
     * output
     *
     * @return void
     */
    public function getOutput()
    {
        return $this->output;
    }

}
