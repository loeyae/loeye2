<?php

/**
 * Request.php
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
use GuzzleHttp\Promise\PromiseInterface;
use GuzzleHttp\Psr7\Uri;
use Psr\Http\Message\ResponseInterface;
use function GuzzleHttp\Psr7\uri_for;

/**
 * Request
 *
 * @author   Zhang Yi <loeyae@gmail.com>
 */
class Request
{

    private $_ch;
    private $_method;
    private $_handle;
    private $_options;

    /**
     *
     * @var ResponseInterface
     */
    private $_res;

    /**
     *
     * @var Uri
     */
    private $_uri;

    /**
     * __construct
     *
     * @return void
     */
    public function __construct()
    {
        $this->_ch = new \GuzzleHttp\Client();
        $this->_options = [
            'connect_timeout' => 15,
            'timeout' => 30,
            'headers' => [],
        ];
    }

    /**
     * __destruct
     *
     * @return void
     */
    public function __destruct()
    {
        unset($this->_ch, $this->_options, $this->_uri, $this->_method, $this->_res);
    }

    /**
     * setOpt
     *
     * @param string $name option name
     * @param mixed $value option value
     *
     * @return void
     */
    public function setOpt($name, $value): void
    {
        $this->_options[$name] = $value;
    }

    /**
     * setRequestUrl
     *
     * @param string $url url
     *
     * @return void
     */
    public function setUrl($url): void
    {
        $this->_uri = new Uri($url);
    }

    /**
     * setMethod
     *
     * @param string $method method
     *
     * @return void
     */
    public function setMethod($method = 'GET'): void
    {
        $this->_method = mb_strtoupper($method);
    }

    /**
     * setProxy
     *
     * @param string $host proxy host
     * @param int $port proxy port
     * @param int $proxyType proxy type ex: CURLPROXY_HTTP|CURLPROXY_SOCKS4|CURLPROXY_SOCKS5
     * @param string $username proxy user name
     * @param string $userpwd proxy user password
     *
     * @return void
     */
    public function setProxy(
        $host, $port, $proxyType = CURLPROXY_HTTP, $username = null, $userpwd = null
    ): void
    {
        if ($port) {
            $host .= ':';
        }
        if ($username && $userpwd) {
            $username .= ':';
        }
        if ($username) {
            $host = '@' . $host;
        }
        $type = 'http';
        if ($proxyType === CURLPROXY_SOCKS4) {
            $type = 'socks4';
        } else if ($proxyType === CURLPROXY_SOCKS5) {
            $type = 'socks5';
        }
        $proxy = sprintf('%s%s%s%s%s', $type, $username, $userpwd, $host, $port);
        $this->_options['proxy'] = $proxy;
    }

    /**
     * setTimeOut
     *
     * @param int $time time
     *
     * @return void
     */
    public function setTimeOut($time = 30): void
    {
        $this->_options['timeout'] = $time;
    }

    /**
     * setHeader
     *
     * @param array $header header
     *
     * @return void
     */
    public function setHeader($header): void
    {
        $this->_options['headers'] = $header;
    }

    /**
     * setCookie
     *
     * @param array $cookies [key => value, ...]
     * @param string $baseUri base url
     *
     * @return void
     */
    public function setCookie($cookies, $baseUri): void
    {
        $uri = uri_for($baseUri);
        $this->_options['cookies'] = CookieJar::fromArray($cookies, $uri->getHost());
    }

    /**
     * setContent
     *
     * @param string $contentType content type
     * @param string $content $content
     *
     * @return void
     */
    public function setContent($contentType, $content): void
    {
        $header = array(
            'Content-Type: ' . $contentType,
            'Content-Length: ' . strlen($content),
        );
        $this->setHeader($header);
        $this->_options['body'] = $content;
    }

    /**
     * setHandle
     *
     * @param resource $handle curl_multi_init
     *
     * @return void
     */
    public function setHandle($handle): void
    {
        $this->_handle = $handle;
    }

    /**
     * execute
     *
     * @return ResponseInterface
     */
    public function execute(): ResponseInterface
    {
        $this->_res = $this->_ch->requestAsync($this->_method, $this->_uri, $this->_options)->wait();
        return $this->_res;
    }

    /**
     *
     * @return PromiseInterface
     */
    public function promise(): PromiseInterface
    {
        return $this->_ch->requestAsync($this->_method, $this->_uri, $this->_options);
    }

    /**
     * set response
     *
     * @param $res
     */
    public function setResponse(ResponseInterface $res): void
    {
        $this->_res = $res;
    }

    /**
     * get Response
     *
     * @return ResponseInterface
     */
    public function getResponse(): ResponseInterface
    {
        return $this->_res;
    }

    /**
     * getResource
     *
     * @return \GuzzleHttp\Client curl
     */
    public function getResource(): \GuzzleHttp\Client
    {
        return $this->_ch;
    }

    /**
     * getHandle
     *
     * @return resource
     */
    public function getHandle()
    {
        return $this->_handle;
    }

    /**
     * getUri
     *
     * @return Uri
     */
    public function getUri(): Uri
    {
        return $this->_uri;
    }

}
