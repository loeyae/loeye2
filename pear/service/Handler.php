<?php

/**
 * Handler.php
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

namespace loeye\service;

/**
 * Description of BaseHandler
 *
 * @author   Zhang Yi <loeyae@gmail.com>
 */
abstract class Handler extends Resource
{

    const ERR_CANNOT_PARSE_JSON   = 1;
    const ERR_MISS_REQUIRE_FIELD  = 2;
    const ERR_INPUT_IS_INVALID    = 3;
    const ERR_ACCESS_DENIED       = 4;
    const ERR_NOT_ALLOWED_INSERT  = 5;
    const ERR_NOT_ALLOWED_UPDATE  = 6;
    const ERR_NOT_ALLOWED_DELETE  = 7;
    const ERR_NOT_ALLOWD_METHOD   = 8;
    const ERR_RECODE_NOT_FOUND    = 9;
    const ERR_METHOD_NOT_FOUND    = 10;
    const ERR_SERVICE_UNAVAILABLE = 11;
    const ERR_RESP_GENRIC         = 12;
    const METHOD_GET    = 'GET';
    const METHOD_POST   = 'POST';
    const METHOD_PUT    = 'PUT';
    const METHOD_DELETE = 'DELETE';

    protected $withDefaultRequestHeader = true;
    protected $withDefaultRequestKey    = 'request_data';
    protected $queryParameter           = array();
    protected $unrawQueryParameter      = array();
    protected $pathParameter            = array();
    protected $req;
    protected $resp;
    protected $cmd;
    protected $output;

    /**
     * app cinfiguration
     *
     * @var \LOEYE\AppConfig
     */
    protected $config;
    public static $ERR_MAPPIG = array(
        self::ERR_CANNOT_PARSE_JSON   => LOEYE_REST_STATUS_BAD_REQUEST,
        self::ERR_MISS_REQUIRE_FIELD  => LOEYE_REST_STATUS_BAD_REQUEST,
        self::ERR_INPUT_IS_INVALID    => LOEYE_REST_STATUS_BAD_REQUEST,
        self::ERR_ACCESS_DENIED       => LOEYE_REST_STATUS_DENIED,
        self::ERR_NOT_ALLOWED_INSERT  => LOEYE_REST_STATUS_DENIED,
        self::ERR_NOT_ALLOWED_UPDATE  => LOEYE_REST_STATUS_DENIED,
        self::ERR_NOT_ALLOWED_DELETE  => LOEYE_REST_STATUS_DENIED,
        self::ERR_NOT_ALLOWD_METHOD   => LOEYE_REST_STATUS_BAD_REQUEST,
        self::ERR_RECODE_NOT_FOUND    => LOEYE_REST_STATUS_NOT_FOUND,
        self::ERR_METHOD_NOT_FOUND    => LOEYE_REST_STATUS_METHOD_NOT_FOUND,
        self::ERR_SERVICE_UNAVAILABLE => LOEYE_REST_STATUS_SERVICE_UNAVAILABLE,
        self::ERR_RESP_GENRIC         => LOEYE_REST_STATUS_SERVICE_UNAVAILABLE,
    );
    public static $ERR_MSG    = array(
        self::ERR_CANNOT_PARSE_JSON   => 'Input data is valid',
        self::ERR_MISS_REQUIRE_FIELD  => 'Input data is valid',
        self::ERR_INPUT_IS_INVALID    => 'Input data is valid',
        self::ERR_ACCESS_DENIED       => 'Access denied',
        self::ERR_NOT_ALLOWED_INSERT  => 'Access denied',
        self::ERR_NOT_ALLOWED_UPDATE  => 'Access denied',
        self::ERR_NOT_ALLOWED_DELETE  => 'Access denied',
        self::ERR_NOT_ALLOWD_METHOD   => 'Access denied',
        self::ERR_RECODE_NOT_FOUND    => 'Resource not found',
        self::ERR_METHOD_NOT_FOUND    => 'HTTP method is not allowed',
        self::ERR_SERVICE_UNAVAILABLE => 'Internal error',
        self::ERR_RESP_GENRIC         => 'Internal error',
    );

    /**
     * _processRequest
     *
     * @param \loeye\service\Request  $req
     * @param \loeye\service\Response $resp
     *
     * @return void
     */
    private function _processRequest(Request $req, Response $resp)
    {
        try {
            $this->init($req, $resp);
            $method = $req->getMethod();
            switch ($method) {
                case self::METHOD_POST:
                    if ($req->getContentLength() == 0) {
                        throw new \loeye\base\Exception(
                                self::$ERR_MSG[self::ERR_CANNOT_PARSE_JSON], self::ERR_CANNOT_PARSE_JSON);
                    }
                    $requestData = $this->_getRequestData($req);
                    $data        = $this->process($requestData);
                    break;
                case self::METHOD_PUT:
                    if ($req->getContentLength() > 1) {
                        $requestData = $this->_getRequestData($req);
                        $data        = $this->process($requestData);
                    }
                    break;
                default:
                    $data = $this->process([]);
                    break;
            }
            if ($this->withDefaultRequestHeader) {
                $this->output['response_data'] = $data;
            } else {
                $this->output = $data;
            }
            $this->render($resp);
        } catch (\loeye\base\Exception $exc) {
            $code = $exc->getCode();
            \loeye\base\Utils::errorLog($exc);
            $this->render($resp, $code, $exc->getMessage());
        } catch (\Exception $exc) {
            $code = $exc->getCode();
            \loeye\base\Utils::errorLog($exc);
            $this->render($resp, $code, 'Server Internal Error');
        }
    }

    /**
     * _getRequestData
     *
     * @param \LOEYE\Request $req request
     *
     * @return mixed
     * @throws \loeye\base\Exception
     */
    private function _getRequestData(Request $req)
    {
        $data        = $req->getContent();
        $requestData = json_decode($data, true);
        if (!is_array($requestData)) {
            throw new \loeye\base\Exception(
                    self::$ERR_MAPPIG[self::ERR_CANNOT_PARSE_JSON], self::ERR_CANNOT_PARSE_JSON);
        }
        if ($this->withDefaultRequestHeader) {
            if (!array_key_exists($this->withDefaultRequestKey, $requestData)) {
                throw new \loeye\base\Exception(
                        self::$ERR_MSG[self::ERR_INPUT_IS_INVALID], self::ERR_INPUT_IS_INVALID);
            }
            $requestData = $requestData[$this->withDefaultRequestKey];
        }
        return $requestData;
    }

    /**
     * get
     *
     * @param \loeye\service\Request  $req
     * @param \loeye\service\Response $resp
     *
     * @return void
     */
    protected function get(Request $req, Response $resp)
    {
        $this->_processRequest($req, $resp);
    }

    /**
     * post
     *
     * @param \loeye\service\Request  $req
     * @param \loeye\service\Response $resp
     *
     * @return void
     */
    protected function post(Request $req, Response $resp)
    {
        $this->_processRequest($req, $resp);
    }

    /**
     * put
     *
     * @param \loeye\service\Request  $req
     * @param \loeye\service\Response $resp
     *
     * @return void
     */
    protected function put(Request $req, Response $resp)
    {
        $this->_processRequest($req, $resp);
    }

    /**
     * delete
     *
     * @param \loeye\service\Request  $req
     * @param \loeye\service\Response $resp
     *
     * @return void
     */
    protected function delete(Request $req, Response $resp)
    {
        $this->_processRequest($req, $resp);
    }

    /**
     * init
     *
     * @return void
     */
    protected function init(Request $req, Response $resp)
    {
        $this->pathParameter = explode('/', $req->getUri()->getPath());
        if (isset($this->pathParameter[3])) {
            $this->cmd = $this->pathParameter[3];
        } else {
            $this->cmd = '';
        }
        $param = filter_input_array(INPUT_GET, FILTER_SANITIZE_FULL_SPECIAL_CHARS, FILTER_FLAG_NO_ENCODE_QUOTES);
        if (!empty($param)) {
            $this->queryParameter = $param;
            array_walk_recursive($this->queryParameter, function (&$item, &$key) {
                $key  = $key;
                $item = filter_var($item, FILTER_SANITIZE_STRING);
            });
            $this->unrawQueryParameter = $param;
            array_walk_recursive($this->unrawQueryParameter, function (&$item, &$key) {
                $key  = $key;
                $item = filter_var($item, FILTER_UNSAFE_RAW);
            });
        }
        $resp->addHeader('Cache-Control', 'public, must-revalidate, max-age=0');
    }

    /**
     *
     * @param \loeye\service\Response $resp    Response instance
     * @param int                     $code    code
     * @param string                  $message message
     */
    protected function render(Response $resp, $code = LOEYE_REST_STATUS_OK, $message = 'OK')
    {
        $status                 = array();
        $status['code']         = $code;
        $status['message']      = $message;
        $this->output['status'] = $status;
        $resp->setContent($this->output);
    }

    /**
     * checkRequiredPathParameter
     *
     * @param int    $position position
     * @param string $field    field name
     *
     * @return string
     * @throws \loeye\base\Exception
     */
    protected function checkRequiredPathParameter($position, $field)
    {
        if (isset($this->pathParameter) && is_array($this->pathParameter) && array_key_exists($position, $this->pathParameter)
        ) {
            return $this->pathParameter[$position];
        } else {
            throw new \loeye\base\Exception(
                    'Missing required field: ' . $field, self::ERR_MISS_REQUIRE_FIELD);
        }
    }

    /**
     * checkNotEmptyPathParameter
     *
     * @param int    $position position
     * @param string $field    field name
     * @param mixed  $default  default value
     *
     * @return string
     * @throws \loeye\base\Exception
     */
    protected function checkNotEmptyPathParameter($position, $field, $default = null)
    {
        $value = $this->checkRequiredPathParameter($position, $field);
        if ($default !== null && $value == $default) {
            return $value;
        } else if (empty($value)) {
            throw new \loeye\base\Exception('Field: ' . $field . 'can not empty', self::ERR_INPUT_IS_INVALID);
        }
        return $value;
    }

    /**
     * checkRequiredParameter
     *
     * @param array  $data data
     * @param string $key  key
     *
     * @return mixed
     * @throws \loeye\base\Exception
     */
    protected function checkRequiredParameter($data, $key)
    {
        if (is_array($data) && array_key_exists($key, $data)) {
            return $data[$key];
        } else {
            throw new \loeye\base\Exception('Missing required field: ' . $key, self::ERR_MISS_REQUIRE_FIELD);
        }
    }

    /**
     * checkNotEmptyParameter
     *
     * @param array  $data    data
     * @param string $key     key
     * @param mixed  $default default value
     *
     * @return mixed
     * @throws \loeye\base\Exception
     */
    protected function checkNotEmptyParameter($data, $key, $default = null)
    {
        $value = $this->checkRequiredParameter($data, $key);
        if ($default !== null && $value == $default) {
            return $value;
        } else if (empty($value)) {
            throw new \loeye\base\Exception('Field: ' . $key . 'can not empty', self::ERR_INPUT_IS_INVALID);
        }
        return $value;
    }

    /**
     * getQueryParam
     *
     * @param string $key key
     *
     * @return null|mixed
     */
    protected function getQueryParam($key)
    {
        if (is_array($this->queryParameter) && !empty($this->queryParameter) && array_key_exists($key, $this->queryParameter)
        ) {
            return $this->queryParameter[$key];
        }
        return null;
    }

    /**
     * process
     *
     * @param array $req request data
     *
     * @return mixed
     */
    abstract protected function process($req);
}
