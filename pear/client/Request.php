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
     * @var \GuzzleHttp\Psr\Http\Message\ResponseInterface
     */
    private $_res;

    /**
     *
     * @var \GuzzleHttp\Psr7\Uri
     */
    private $_uri;

    /**
     * __construct
     *
     * @return void
     */
    public function __construct()
    {
        $this->_ch      = new \GuzzleHttp\Client();
        $this->_options = [
            'connect_timeout' => 15,
            'timeout'         => 30,
            'headers'         => [],
        ];
    }

    /**
     * __destruct
     *
     * @return void
     */
    public function __destruct()
    {
        unset($this->_ch);
        unset($this->_options);
        unset($this->_uri);
        unset($this->_method);
        unset($this->_res);
    }

    /**
     * setOpt
     *
     * @param string $name  option name
     * @param mixed  $value option value
     *
     * @return void
     */
    public function setOpt($name, $value)
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
    public function setUrl($url)
    {
        $this->_uri = new \GuzzleHttp\Psr7\Uri($url);
    }

    /**
     * setMethod
     *
     * @param string $mothed method
     *
     * @return void
     */
    public function setMethod($mothed = 'GET')
    {
        $this->_method = mb_strtoupper($mothed);
    }

    /**
     * setProxy
     *
     * @param string $host      proxy host
     * @param inpt   $port      proxy port
     * @param int    $proxyType proxy type ex: CURLPROXY_HTTP|CURLPROXY_SOCKS4|CURLPROXY_SOCKS5
     * @param string $username  proxy user name
     * @param string $userpwd   proxy user password
     *
     * @return void
     */
    public function setProxy(
            $host, $port, $proxyType = CURLPROXY_HTTP, $username = null, $userpwd = null
    )
    {
        if ($port) {
            $host .= ':';
        }
        $proxy = sprintf("%s%s");
        if ($username && $userpwd) {
            $username .= ':';
        }
        if ($username) {
            $host = '@' . $host;
        }
        $type = 'http';
        if ($proxyType == CURLPROXY_SOCKS4) {
            $type = 'socks4';
        } else if ($proxyType == CURLPROXY_SOCKS5) {
            $type = 'socks5';
        }
        $proxy                   = sprintf('%s%s%s%s%s', $type, $username, $userpwd, $host, $port);
        $this->_options['proxy'] = $proxy;
    }

    /**
     * setTimeOut
     *
     * @param int $time time
     *
     * @return void
     */
    public function setTimeOut($time = 30)
    {
        $this->_options['timeout'] = $time;
    }

    /**
     * setHeader
     *
     * @param array $header header
     *
     * @return array
     */
    public function setHeader($header)
    {
        $this->_options['headers'] = $header;
    }

    /**
     * setCookie
     *
     * @param array  $cookies [key => value, ...]
     * @param string $baseUri base url
     *
     * @return void
     */
    public function setCookie($cookies, $baseUri)
    {
        $uri                       = \GuzzleHttp\Psr7\uri_for($baseUri);
        $this->_options['cookies'] = \GuzzleHttp\Cookie\CookieJar::fromArray($cookies, $uri->getHost());
    }

    /**
     * setContent
     *
     * @param string $contentType content type
     * @param string $content     $content
     *
     * @return void
     */
    public function setContent($contentType, $content)
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
    public function setHandle($handle)
    {
        $this->_handle = $handle;
    }

    /**
     * execute
     *
     * @return execute
     */
    public function execute()
    {
        $this->_res = $this->_ch->requestAsync($this->_method, $this->_uri, $this->_options)->wait();
        return $this->_res;
    }

    /**
     *
     * @return type
     */
    public function promise()
    {
        return $this->_ch->requestAsync($this->_method, $this->_uri, $this->_options);
    }

    /**
     * set response
     *
     * @param \GuzzleHttp\Psr\Http\Message\ResponseInterface $res
     */
    public function setResponse(\Psr\Http\Message\ResponseInterface $res)
    {
        $this->_res = $res;
    }

    /**
     * get Response
     *
     * @return \GuzzleHttp\Psr\Http\Message\ResponseInterface
     */
    public function getResponse()
    {
        return $this->_res;
    }

    /**
     * getResource
     *
     * @return resource curl
     */
    public function getResource()
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
     * @return \GuzzleHttp\Psr7\Uri
     */
    public function getUri()
    {
        return $this->_uri;
    }

}
