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
 * @version  GIT: $Id: Zhang Yi $
 * @link     https://github.com/loeyae/loeye.git
 */

namespace loeye\client;

/**
 * Response
 *
 * @author   Zhang Yi <loeyae@gmail.com>
 */
class Response
{

    const STATUS_OK = 200;

    /**
     * request
     *
     * @var \loeye\client\Request
     */
    private $_req;

    /**
     * response
     *
     * @var \GuzzleHttp\Psr\Http\Message\ResponseInterface
     */
    private $_res;

    /**
     * cookie
     *
     * @var \GuzzleHttp\Cookie\CookieJar
     */
    private $_cookies;

    /**
     *
     * @param \loeye\client\Request $request
     *
     */
    public function __construct(Request $request)
    {
        $this->_req     = $request;
        $this->_res     = $request->getResponse();
        $this->_cookies = new \GuzzleHttp\Cookie\CookieJar();
        if ($cookieHeader   = $response->getHeader('Set-Cookie')) {
            foreach ($cookieHeader as $cookie) {
                $sc = SetCookie::fromString($cookie);
                if (!$sc->getDomain()) {
                    $sc->setDomain($request->getUri()->getHost());
                }
                if (0 !== strpos($sc->getPath(), '/')) {
                    $sc->setPath($this->getCookiePathFromRequest($request));
                }
                $this->_cookies->setCookie($sc);
            }
        }
    }

    /**
     * getRequest
     *
     * @return \loeye\client\Request
     */
    public function getRequest()
    {
        return $this->_req;
    }

    /**
     * getContent
     *
     * @return string
     */
    public function getContent()
    {
        return $this->_res->getBody()->getContents();
    }

    /**
     * getStatusCode
     *
     * @return int
     */
    public function getStatusCode()
    {
        return $this->_res->getStatusCode();
    }

    /**
     * getErrorCode
     *
     * @return int
     */
    public function getErrorCode()
    {
        return $this->_res->getStatusCode() == self::STATUS_OK ? 0 : $this->_res->getStatusCode();
    }

    /**
     * getErrorMsg
     *
     * @return string
     */
    public function getErrorMsg()
    {
        return $this->_res->getStatusCode() == self::STATUS_OK ? '' : $this->_res->getReasonPhrase();
    }

    /**
     * getHeader
     * @param string|null $name name
     *
     * @return array|string
     */
    public function getHeader($name = null)
    {
        if ($name) {
            return $this->_res->getHeader($name);
        }
        return $this->_res->getHeaders();
    }

    /**
     * getCookie
     *
     * @param string|null $name name
     *
     * @return array
     */
    public function getCookie($name = null)
    {
        if ($name) {
            return $this->_cookies->getCookieByName($name)->toArray();
        }
        return $this->_cookies->toArray();
    }

    /**
     * getCookieJar
     *
     * @return \GuzzleHttp\Cookie\CookieJar
     */
    public function getCookieJar()
    {
        return $this->_cookies;
    }

}
