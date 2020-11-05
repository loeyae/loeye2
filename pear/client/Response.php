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

namespace loeye\client;

use GuzzleHttp\Cookie\CookieJar;
use GuzzleHttp\Cookie\SetCookie;
use Psr\Http\Message\ResponseInterface;

/**
 * Response
 *
 * @author   Zhang Yi <loeyae@gmail.com>
 */
class Response
{

    public const STATUS_OK = 200;

    /**
     * request
     *
     * @var Request
     */
    private $_req;

    /**
     * response
     *
     * @var ResponseInterface
     */
    private $_res;

    /**
     * cookie
     *
     * @var CookieJar
     */
    private $_cookies;

    /**
     *
     * @param Request $request
     *
     */
    public function __construct(Request $request)
    {
        $this->_req     = $request;
        $this->_res     = $request->getResponse();
        $this->_cookies = new CookieJar();
        if ($cookieHeader   = $this->_res->getHeader('Set-Cookie')) {
            foreach ($cookieHeader as $cookie) {
                $sc = SetCookie::fromString($cookie);
                if (!$sc->getDomain()) {
                    $sc->setDomain($request->getUri()->getHost());
                }
                if (0 !== strpos($sc->getPath(), '/')) {
                    $sc->setPath($request->getUri()->getPath());
                }
                $this->_cookies->setCookie($sc);
            }
        }
    }

    /**
     * getRequest
     *
     * @return Request
     */
    public function getRequest(): Request
    {
        return $this->_req;
    }

    /**
     * getContent
     *
     * @return string
     */
    public function getContent(): string
    {
        return $this->_res->getBody()->getContents();
    }

    /**
     * getStatusCode
     *
     * @return int
     */
    public function getStatusCode(): int
    {
        return $this->_res->getStatusCode();
    }

    /**
     * getErrorCode
     *
     * @return int
     */
    public function getErrorCode(): int
    {
        return $this->_res->getStatusCode() === self::STATUS_OK ? 0 : $this->_res->getStatusCode();
    }

    /**
     * getErrorMsg
     *
     * @return string
     */
    public function getErrorMsg(): string
    {
        return $this->_res->getStatusCode() === self::STATUS_OK ? '' : $this->_res->getReasonPhrase();
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
     * @return mixed
     */
    public function getCookie($name = null)
    {
        if ($name) {
            $cookie = $this->_cookies->getCookieByName($name);
            return $cookie ? $cookie->toArray() : null;
        }
        return $this->_cookies->toArray();
    }

    /**
     * getCookieJar
     *
     * @return CookieJar
     */
    public function getCookieJar(): CookieJar
    {
        return $this->_cookies;
    }

}
