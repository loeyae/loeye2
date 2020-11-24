<?php

/**
 * OutputPlugin.php
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

namespace loeye\plugin;

use loeye\base\{Context, Exception, Factory, Utils};
use Doctrine\ORM\Tools\Pagination\Paginator;
use loeye\database\Entity;
use loeye\error\BusinessException;
use loeye\error\ValidateError;
use loeye\lib\ModuleParse;
use loeye\std\Plugin;
use ReflectionException;
use const loeye\base\RENDER_TYPE_SEGMENT;

/**
 * OutputPlugin
 *
 * @author   Zhang Yi <loeyae@gmail.com>
 */
class OutputPlugin implements Plugin
{

    /**
     * output data key
     * @var string
     */
    protected $dataKey = 'output_data';
    
    protected $reponseCode = LOEYE_REST_STATUS_OK;
    
    protected $responseMsg = 'OK';

    /**
     * process
     *
     * @param Context $context context
     * @param array $inputs inputs
     *
     * @return mixed
     * @throws ReflectionException
     * @throws \Psr\Cache\InvalidArgumentException
     * @throws \Throwable
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function process(Context $context, array $inputs)
    {
        $format     = Utils::getData($inputs, 'format', $context->getFormat( 'json'));
        $data       = array();
        $outDataKey = Utils::getData($inputs, $this->dataKey, null);
        if ($outDataKey === null) {
            $outDataKey = Utils::getData($inputs, 'data', null);
        }
        if (!empty($outDataKey)) {
            $data = Utils::getData($context, $outDataKey);
        } 
        if (empty($data) && isset($inputs['error'])) {
            $errors = Utils::getErrors($context, $inputs, $inputs['error']);
            if (!empty($errors) && $error = current($errors)) {
                $this->reponseCode = LOEYE_REST_STATUS_BAD_REQUEST;
                $this->responseMsg = 'error';
                if ($error instanceof ValidateError) {
                    $this->reponseCode = $error->getCode();
                    $this->responseMsg = $error->getMessage();
                    $data = $error->getValidateMessage();
                } elseif ($error instanceof \Throwable) {
                    $this->responseMsg = $error->getMessage();
                } else {
                    $this->responseMsg = $error;
                }
            }
        }
        if ($data instanceof Entity) {
            $data = Utils::entity2array(Factory::db()->em(), $data);
        } elseif ($data instanceof  Paginator) {
            $data = Utils::paginator2array(Factory::db()->em(), $data);
        } elseif (is_array($data)) {
            $data = Utils::entities2array(Factory::db()->em(), $data);
        }
        $redirect  = null;
        $routerKey = Utils::getData($inputs, 'router_key');
        if (!empty($routerKey)) {
            $parameter = Utils::getData($inputs, 'parameter', array());
            $router    = $context->getRouter();
            $url       = $router->generate($routerKey, $parameter);
            $redirect  = $url;
        } else {
            $url = Utils::getData($inputs, 'url');
            if (!empty($url)) {
                $redirect = $url;
            }
        }
        $status  = Utils::getData($inputs, 'code', $this->reponseCode);
        $header = Utils::getData($inputs, 'header', []);
        if (!empty($header)) {
            foreach ($header as $key => $value) {
                $context->getResponse()->addHeader($key, $value);
            }
        }
        if ($format === RENDER_TYPE_SEGMENT) {
            $context->getResponse()->setStatusCode($status);
            if (!empty($redirect)) {
                header('Location: ' . $redirect, true, LOEYE_REST_STATUS_REDIRECT);
            }
            $message = Utils::getData($inputs, 'msg');
            if ($message !== null && $data !== null && is_string($message) && is_string($data)) {
                $message .= $data;
                $message = $this->printf($message, $context, $inputs);
                $context->getResponse()->addOutput($message);
            } else {
                if ($message !== null) {
                    $message = $this->printf($message, $context, $inputs);
                    $context->getResponse()->addOutput($message);
                }
                if ($data !== null) {
                    $context->getResponse()->addOutput($data, 'data');
                }
            }
        } else {
            $context->getResponse()->setFormat($format);
            $context->getResponse()->addOutput($status, 'status');
            $message = Utils::getData($inputs, 'msg', $this->responseMsg);
            if ($message !== null) {
                if (is_array($message)) {
                    foreach ($message as $key => $msg) {
                        $result = ModuleParse::conditionResult($key, $context);
                        if ($result === true) {
                            $msg = $this->printf($msg, $context, $inputs);
                            $context->getResponse()->addOutput($msg, 'message');
                            break;
                        }
                    }
                } else {
                    $message = $this->printf($message, $context, $inputs);
                    $context->getResponse()->addOutput($message, 'message');
                }
            }
            $context->getResponse()->addOutput($data, 'data');
            if (!empty($redirect)) {
                $context->getResponse()->addOutput($redirect, 'redirect');
            }
        }
        if (isset($inputs['force']) && $inputs['force'] == true) {
            $render = Factory::getRender($context->getResponse()->getFormat());
            $render->header($context->getResponse());
            $render->output($context->getResponse());
            exit;
        } else if (isset($inputs['break']) && $inputs['break'] == true) {
            return false;
        } else {
            $context->getResponse()->setRenderId(null);
            return false;
        }
    }

    /**
     * printf
     *
     * @param string              $message message
     * @param Context $context context
     * @param array               $inputs  inputs
     *
     * @return string
     */
    protected function printf($message, Context $context, array $inputs): string
    {
        $replace = [];
        if (isset($inputs['replace'])) {
            $replace = $inputs['replace'];
        } elseif (isset($inputs['rep_key'])) {
            $replace = Utils::getData($context, $inputs['rep_key'], null);
        }
        if (!is_array($replace)) {
            Utils::throwException(BusinessException::INVALID_PARAMETER_MSG,
                BusinessException::INVALID_PARAMETER_CODE, [], BusinessException::class);
        }
        $translator = $context->get('loeye_translator');
        if (!$translator) {
            $translator = Factory::translator();
        }
        return $translator->getString($message, $replace);
    }

}
