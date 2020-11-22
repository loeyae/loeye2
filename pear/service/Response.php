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
     * @var string
     */
    private $_serverProtocol;
    /**
     * @var int
     */
    private $_statusCode;

    /**
     * @var string
     */
    private $_statusMessage;
    /**
     * @var string
     */
    private $_contentType;
    /**
     * @var string
     */
    private $_responseData;

    /**
     * __construct
     *
     * @param Request $req request
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
     * setStatusMessage
     *
     * @param string $message message
     *
     * @return void
     */
    public function setStatusMessage($message): void
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
    public function setContent($data, $contentType = null): void
    {
        if (!empty($contentType)) {
            $this->_contentType = $contentType;
        }
        $this->output = $data;
    }

    /**
     * {@inheritDoc}
     */
    public function setHeaders(): void
    {
        $header = $this->_serverProtocol . ' ' . $this->_statusCode . ' ' . $this->_statusMessage;
        header($header);
        parent::setHeaders();
        if (!array_key_exists('Content-Type', $this->header)) {
            header('Content-Type:'. $this->_contentType);
        }
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
