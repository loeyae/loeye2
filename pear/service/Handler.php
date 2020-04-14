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

use loeye\base\Logger;
use loeye\database\Entity;
use loeye\base\Exception;
use loeye\base\Utils;
use loeye\error\RequestParameterException;
use loeye\error\ValidateError;
use loeye\validate\Validation;
use loeye\validate\Validator;
use ReflectionException;

/**
 * Description of BaseHandler
 *
 * @author   Zhang Yi <loeyae@gmail.com>
 */
abstract class Handler extends Resource
{

    public const METHOD_GET = 'GET';
    public const METHOD_POST = 'POST';
    public const METHOD_PUT = 'PUT';
    public const METHOD_DELETE = 'DELETE';

    protected $withDefaultRequestHeader = true;
    protected $withDefaultRequestKey = 'request_data';
    protected $queryParameter = array();
    protected $unRawQueryParameter = array();
    protected $pathParameter = array();
    protected $cmd;
    protected $output;
    protected $entityClass;
    protected $group;

    /**
     * _processRequest
     *
     * @param Request $req
     * @param Response $resp
     *
     * @return void
     * @throws RequestParameterException
     * @throws ReflectionException
     * @throws Exception
     */
    private function _processRequest(Request $req, Response $resp): void
    {
        $this->init($req, $resp);
        $method = $req->getMethod();
        $data = null;
        switch ($method) {
            case self::METHOD_POST:
                if ($req->getContentLength() === 0) {
                    throw new RequestParameterException(RequestParameterException::REQUEST_BODY_EMPTY_MSG, RequestParameterException::REQUEST_BODY_EMPTY_CODE);
                }
                $requestData = $this->_getRequestData($req);
                $data = $this->process($requestData);
                break;
            case self::METHOD_PUT:
                if ($req->getContentLength() > 1) {
                    $requestData = $this->_getRequestData($req);
                    $data = $this->process($requestData);
                }
                break;
            default:
                $data = $this->process([]);
                break;
        }
        if (is_array($data) && isset($data[0]) && $data[0] instanceof Entity) {
            $data = Utils::entities2array($this->context->db()->entityManager(), $data);
        } elseif ($data instanceof Entity) {
            $data = Utils::entity2array($this->context->db()->entityManager(), $data);
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
     * @param Request $req request
     *
     * @return mixed
     * @throws Exception
     */
    private function _getRequestData(Request $req)
    {
        $data = $req->getContent();
        $requestData = json_decode($data, true);
        if (!is_array($requestData)) {
            throw new RequestParameterException(RequestParameterException::REQUEST_BODY_EMPTY_MSG,
                RequestParameterException::REQUEST_BODY_EMPTY_CODE);
        }
        if ($this->withDefaultRequestHeader) {
            if (!array_key_exists($this->withDefaultRequestKey, $requestData)) {
                throw new RequestParameterException(RequestParameterException::$PARAMETER_ERROR_MSG_TEMPLATES['parameter_required'],
                    RequestParameterException::REQUEST_PARAMETER_ERROR_CODE,
                    ['field' => $this->withDefaultRequestKey]);
            }
            $requestData = $requestData[$this->withDefaultRequestKey];
        }
        return $requestData;
    }

    /**
     * get
     *
     * @param Request $req
     * @param Response $resp
     *
     * @return void
     * @throws Exception
     * @throws ReflectionException
     * @throws RequestParameterException
     */
    protected function get(Request $req, Response $resp): void
    {
        $this->_processRequest($req, $resp);
    }

    /**
     * post
     *
     * @param Request $req
     * @param Response $resp
     *
     * @return void
     * @throws Exception
     * @throws ReflectionException
     * @throws RequestParameterException
     */
    protected function post(Request $req, Response $resp): void
    {
        $this->_processRequest($req, $resp);
    }

    /**
     * put
     *
     * @param Request $req
     * @param Response $resp
     *
     * @return void
     * @throws Exception
     * @throws ReflectionException
     * @throws RequestParameterException
     */
    protected function put(Request $req, Response $resp): void
    {
        $this->_processRequest($req, $resp);
    }

    /**
     * delete
     *
     * @param Request $req
     * @param Response $resp
     *
     * @return void
     * @throws Exception
     * @throws ReflectionException
     * @throws RequestParameterException
     */
    protected function delete(Request $req, Response $resp): void
    {
        $this->_processRequest($req, $resp);
    }

    /**
     * init
     *
     * @param Request $req
     * @param Response $resp
     * @return void
     */
    protected function init(Request $req, Response $resp): void
    {
        $this->pathParameter = $req->getPathVariable();
        $this->cmd = $this->pathParameter['handler'] ?? '';
        $param = filter_input_array(INPUT_GET, FILTER_SANITIZE_FULL_SPECIAL_CHARS, FILTER_FLAG_NO_ENCODE_QUOTES);
        if (!empty($param)) {
            $this->queryParameter = $param;
            array_walk_recursive($this->queryParameter, static function (&$item) {
                $item = filter_var($item, FILTER_SANITIZE_STRING);
            });
            $this->unRawQueryParameter = $param;
            array_walk_recursive($this->unRawQueryParameter, static function (&$item) {
                $item = filter_var($item, FILTER_UNSAFE_RAW);
            });
        }
        $resp->addHeader('Cache-Control', 'public, must-revalidate, max-age=0');
    }

    /**
     *
     * @param Response $resp Response instance
     * @param int $code code
     * @param string $message message
     */
    protected function render(Response $resp, $code = LOEYE_REST_STATUS_OK, $message = 'OK'): void
    {
        $status = array();
        $status['code'] = $code;
        $status['message'] = $message;
        $this->output['status'] = $status;
        $resp->setContent($this->output);
    }

    /**
     * checkRequiredPathParameter
     *
     * @param string $key key name
     *
     * @return string|null
     * @throws Exception
     */
    protected function checkRequiredPathParameter($key): ?string
    {
        if (array_key_exists($key, $this->pathParameter)
        ) {
            return $this->pathParameter[$key];
        }

        throw new RequestParameterException(RequestParameterException::$PARAMETER_ERROR_MSG_TEMPLATES['path_var_required'],
            RequestParameterException::REQUEST_PARAMETER_ERROR_CODE, ['field' => $key]);
    }

    /**
     * checkNotEmptyPathParameter
     *
     * @param string $key key name
     * @param mixed $default default value
     *
     * @return string
     * @throws Exception
     */
    protected function checkNotEmptyPathParameter($key, $default = null): string
    {
        $value = $this->checkRequiredPathParameter($key);
        if ($default !== null && $value === $default) {
            return $value;
        }

        if ($value !== 0 && empty($value)) {
            throw new RequestParameterException(RequestParameterException::$PARAMETER_ERROR_MSG_TEMPLATES['path_var_not_empty'],
                RequestParameterException::REQUEST_PARAMETER_ERROR_CODE, ['field' => $key]);
        }
        return $value;
    }

    /**
     * validate
     *
     * @param $data
     * @throws ValidateError
     */
    protected function validate($data): void
    {
        if ($this->entityClass) {
            try {
                $entityObject = Utils::source2entity($data, $this->entityClass);
                $validator = Validation::createValidator();
                $violationList = $validator->validate($entityObject, null, $this->group);
                $validateError = Validator::buildErrmsg($violationList, Validator::initTranslator
                ($this->context->getAppConfig()));
                throw new ValidateError($validateError,ValidateError::DEFAULT_ERROR_MSG,
                    ValidateError::DEFAULT_ERROR_CODE);
            } catch (ReflectionException $e) {
                Logger::exception($e);
                $validateError = ['Entity Class Not Exists.'];
                throw new ValidateError($validateError,ValidateError::DEFAULT_ERROR_MSG,
                    ValidateError::DEFAULT_ERROR_CODE);
            }
        }
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
        if (is_array($this->queryParameter) && !empty($this->queryParameter) && array_key_exists($key,
                $this->queryParameter)
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
