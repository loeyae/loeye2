<?php

/**
 * Client.php
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

use loeye\base\Configuration;
use loeye\error\BusinessException;
use loeye\std\ConfigTrait;

/**
 * Client
 *
 * @author   Zhang Yi <loeyae@gmail.com>
 */
abstract class Client
{

    use ConfigTrait;

    /**
     * request status ok
     *
     * @var int
     */
    public const REQUEST_STATUS_OK = 200;

    /**
     * @var string config bundle
     */
    public const BUNDLE = 'client';

    /**
     * service base url
     * @var string
     */
    protected $baseUrl;

    /**
     * Configuration
     *
     * @var Configuration
     */
    protected $config;
    protected $timeout = 5;
    private $_headers = array();
    private $_isParallel = false;

    /**
     *
     * @var Request[]
     */
    private $_parallelRequest = array();
    private $_parallelRequestInfo = array();

    /**
     * __construct
     *
     * @param string $bundle bundle
     *
     * @return void
     * @throws BusinessException
     */
    public function __construct($bundle = null)
    {
        $this->config = $this->propertyConfig(static::BUNDLE, $bundle);
        $config = $this->config->get('service');
        if (empty($config['server_url']) || !is_string($config['server_url'])) {
            throw new BusinessException(BusinessException::INVALID_CONFIG_SET_MSG,
                BusinessException::INVALID_CONFIG_SET_CODE, ['setting' => 'server_url']);
        }
        $this->baseUrl = $config['server_url'];
        if (!empty($config['timeout']) && $config['timeout'] > 0 && $config['timeout'] <= 30) {
            $this->timeout = $config['timeout'];
        }
        $this->reset();
    }

    /**
     * setHeader
     *
     * @param string $name name
     * @param string $value value
     *
     * @return void
     */
    public function setHeader($name, $value): void
    {
        $this->_headers[$name] = $value;
    }

    /**
     * getHeader
     *
     * @return array
     */
    public function getHeader(): array
    {
        return $this->_headers;
    }

    /**
     * setParallel
     *
     * @return void
     */
    public function setParallel(): void
    {
        $this->_isParallel = true;
    }

    /**
     * getParallelMode
     *
     * @return bool
     */
    public function getParallelMode(): bool
    {
        return $this->_isParallel;
    }

    /**
     * getParallelRequest
     *
     * @return Request[]
     */
    public function getParallelRequest(): array
    {
        return $this->_parallelRequest;
    }

    /**
     * getParallelRequestInfo
     *
     * @return array
     */
    public function getParallelRequestInfo(): array
    {
        return $this->_parallelRequestInfo;
    }

    /**
     * request
     *
     * @param string $cmd command
     * @param Request $req request
     * @param mixed                 &$ret ret
     *
     * @return void
     */
    protected function request($cmd, Request $req, &$ret): void
    {
        $header = array();
        if (!isset($this->_headers['Expect'])) {
            $this->_headers['Expect'] = ' ';
        }
        foreach ($this->_headers as $name => $value) {
            $header[] = $name . ': ' . $value;
        }
        if (!empty($header)) {
            $req->setHeader($header);
        }
        if ($this->_isParallel) {
            $this->_parallelRequest($cmd, $req, $ret);
        } else {
            $ret = $this->_directRequest($cmd, $req);
        }
        return $ret;
    }

    /**
     * reset
     *
     * @return void
     */
    public function reset(): void
    {
        $this->_isParallel = false;
        $this->_parallelRequest = array();
        $this->_parallelRequestInfo = array();
    }

    /**
     * _parallelRequest
     *
     * @param string $cmd command
     * @param Request $req request
     * @param mixed                   &$ret ret
     *
     * @return void
     */
    private function _parallelRequest($cmd, Request $req, &$ret = false): void
    {
        $this->_parallelRequestInfo[] = array(
            'cmd' => $cmd,
            'ret' => & $ret,
        );
        $this->_parallelRequest[] = $req;
    }

    /**
     * _directRequest
     *
     * @param string $cmd command
     * @param Request $req request
     *
     * @return mixed
     */
    private function _directRequest($cmd, Request $req)
    {
        $req->execute();
        $resp = new Response($req);

        return $this->responseHandle($cmd, $resp);
    }

    /**
     * onComplete
     *
     * @param Request $req request
     * @param int $index index
     *
     * @return mixed
     */
    public function onComplete(Request $req, $index = 0)
    {
        $resp = new Response($req);
        $cmd = $this->_parallelRequestInfo[$index]['cmd'];
        $this->_parallelRequestInfo[$index]['ret'] = $this->responseHandle($cmd, $resp);
        return $this->_parallelRequestInfo[$index]['ret'];
    }

    /**
     * responseHandle
     *
     * @param string $cmd command
     * @param Response $resp response
     *
     * @return mixed
     */
    abstract public function responseHandle($cmd, Response $resp);

    /**
     * setReq
     *
     * @param Request $req request
     * @param string $method method
     * @param string $path path
     * @param array $requestData request data
     *
     * @return void
     */
    protected function setReq(Request $req, $method, $path, $requestData = null): void
    {
        $req->setMethod($method);
        $req->setTimeOut($this->timeout);
        $url = $this->baseUrl . $path;
        $req->setUrl($url);
        if ($requestData !== null) {
            $req->setContent('application/json',
                json_encode($requestData, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
        }
    }

}
