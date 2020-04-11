<?php

/**
 * ServerRequest.php
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

use const loeye\base\RENDER_TYPE_JSON;
use const loeye\base\RENDER_TYPE_SEGMENT;
use const loeye\base\RENDER_TYPE_XML;

/**
 * Request
 *
 * @author   Zhang Yi <loeyae@gmail.com>
 */
class Request extends \loeye\std\Request
{

    private $_content;

    protected $_allowedFormatType = array(

        RENDER_TYPE_SEGMENT,
        RENDER_TYPE_XML,
        RENDER_TYPE_JSON,
    );

    /**
     * offsetExists
     *
     * @param string $offset offset
     *
     * @return bool
     */
    public function offsetExists($offset): bool
    {
        if ($offset === 'content'):
            return $this->_content ? true : false;
        elseif ($offset === 'contentLength'):
            return $this->_content ? true : false;
        else:
            return parent::offsetExists($offset);
        endif;
    }

    /**
     * offsetGet
     *
     * @param string $offset offset
     *
     * @return mixed
     */
    public function offsetGet($offset)
    {
        if ($offset === 'content'):
            return $this->getContent();
        elseif ($offset === 'contentLength'):
            return $this->getContentLength();
        else:
            return parent::offsetGet($offset);
        endif;
    }

    /**
     * getFormatType
     *
     * @return string
     */
    public function getFormatType(): string
    {
        $format = $this->getParameterGet('fmt') ?? RENDER_TYPE_JSON;
        if (in_array($format, $this->_allowedFormatType, true)) {
            return $format;
        }

        return RENDER_TYPE_SEGMENT;
    }

    /**
     * getContent
     *
     * @return string
     */
    public function getContent(): string
    {
        $this->_content ?: $this->_content = file_get_contents('php://input');
        return $this->_content;
    }

    /**
     * getContentLength
     *
     * @return int
     */
    public function getContentLength(): int
    {
        return strlen($this->getContent());
    }

    /**
     * getRemoteAddr
     *
     * @return null|string
     */
    public function getRemoteAddr(): ?string
    {
        if (filter_has_var(INPUT_SERVER, 'REMOTE_ADDR')) {
            return filter_input(INPUT_SERVER, 'REMOTE_ADDR');
        }
        return null;
    }

    /**
     * getServerProtocol
     *
     * @return string
     */
    public function getServerProtocol(): string
    {
        return $this->server['SERVER_PROTOCOL'] ?? 'HTTP/1.0';
    }

//
//    /**
//     * getFormatType
//     *
//     * @return mixed
//     */
//    public function getFormatType()
//    {
//        $queryData = $this->getUri()->getQueryData();
//        return isset($queryData['fmt']) ? $queryData['fmt'] : null;
//    }
}
