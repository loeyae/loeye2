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
 * @version  2018-07-23 22:44:28
 * @link     https://github.com/loeyae/loeye2.git
 */

namespace loeye\service;

use \loeye\error\RequestParameterException;

/**
 * Description of BaseHandler
 *
 * @author   Zhang Yi <loeyae@gmail.com>
 */
abstract class Handler extends Resource
{

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
     * _processRequest
     *
     * @param \loeye\service\Request  $req
     * @param \loeye\service\Response $resp
     *
     * @return void
     */
    private function _processRequest(Request $req, Response $resp)
    {
        $this->init($req, $resp);
        $method = $req->getMethod();
        switch ($method) {
            case self::METHOD_POST:
                if ($req->getContentLength() == 0) {
                    throw new RequestParameterException(RequestParameterException::REQUEST_BODY_EMPTY_MSG, RequestParameterException::REQUEST_BODY_EMPTY_CODE);
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
        if (is_array($data)) {
            $data = \loeye\base\Utils::entities2array($this->context->db()->entityManager(), $data);
        } elseif (is_object($data)) {
            $data = \loeye\base\Utils::entity2array($this->context->db()->entityManager(), $data);
        }
        if ($this->withDefaultRequestHeader) {
            $this->output['response_data'] = $data;
        } else {
            $this->output = $data;
        }
        $this->render($resp);
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
            throw new RequestParameterException(RequestParameterException::REQUEST_BODY_EMPTY_MSG, RequestParameterException::REQUEST_BODY_EMPTY_CODE);
        }
        if ($this->withDefaultRequestHeader) {
            if (!array_key_exists($this->withDefaultRequestKey, $requestData)) {
                throw new RequestParameterException(RequestParameterException::$PARAMETER_ERROR_MSG_TEMPLATES["parameter_required"], RequestParameterException::REQUEST_PARAMETER_ERROR_CODE, ["{field}" => $this->withDefaultRequestKey]);
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
            throw new RequestParameterException(RequestParameterException::$PARAMETER_ERROR_MSG_TEMPLATES['path_var_required'], RequestParameterException::REQUEST_PARAMETER_ERROR_CODE, ["{field}"=>$field]);
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
            throw new RequestParameterException(RequestParameterException::$PARAMETER_ERROR_MSG_TEMPLATES['path_var_not_empty'], RequestParameterException::REQUEST_PARAMETER_ERROR_CODE, ["{field}"=>$field]);
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
            throw new RequestParameterException(RequestParameterException::$PARAMETER_ERROR_MSG_TEMPLATES['parameter_not_empty'], RequestParameterException::REQUEST_PARAMETER_ERROR_CODE, ["{field}"=>$key]);
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
            throw new RequestParameterException(RequestParameterException::$PARAMETER_ERROR_MSG_TEMPLATES['parameter_required'], RequestParameterException::REQUEST_PARAMETER_ERROR_CODE, ["{field}"=>$key]);
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
