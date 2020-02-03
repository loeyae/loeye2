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

use loeye\base\{
    Utils,
    Context,
    Factory
};

/**
 * OutputPlugin
 *
 * @author   Zhang Yi <loeyae@gmail.com>
 */
class OutputPlugin extends \loeye\std\Plugin
{

    /**
     * output data key
     * @var string
     */
    protected $dataKey = 'output_data';

    /**
     * process
     *
     * @param \loeye\base\Context $context context
     * @param array               $inputs  inputs
     *
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function process(\loeye\base\Context $context, array $inputs)
    {
        $format     = \loeye\base\Utils::getData($inputs, 'format', 'json');
        $data       = array();
        $outDataKey = \loeye\base\Utils::getData($inputs, $this->dataKey, null);
        if ($outDataKey === null) {
            $outDataKey = \loeye\base\Utils::getData($inputs, 'data', null);
        }
        if (!empty($outDataKey)) {
            $data = \loeye\base\Utils::getData($context, $outDataKey);
        } else if (isset($inputs['error'])) {
            $data = \loeye\base\Utils::getErrors($context, $inputs, $inputs['error']);
        }
        $redirect  = null;
        $routerKey = \loeye\base\Utils::getData($inputs, 'router_key');
        if (!empty($routerKey)) {
            $parameter = \loeye\base\Utils::getData($inputs, 'parameter', array());
            $router    = $context->getRouter();
            $url       = $router->generate($routerKey, $parameter);
            $redirect  = $url;
        } else {
            $url = \loeye\base\Utils::getData($inputs, 'url');
            if (!empty($url)) {
                $redirect = $url;
            }
        }
        if ($format == \loeye\base\RENDER_TYPE_SEGMENT) {
            $status = \loeye\base\Utils::getData($inputs, 'code');
            $header = \loeye\base\Utils::getData($inputs, 'header', null);
            if (!empty($header) && !empty($status)) {
                header($header, true, $status);
            } else if (!empty($header)) {
                header($header);
            }
            if (!empty($redirect)) {
                header('Location: ' . $redirect, true, 302);
            }
            $message = \loeye\base\Utils::getData($inputs, 'msg');
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
            $header = \loeye\base\Utils::getData($inputs, 'header', null);
            if (!empty($header)) {
                header($header);
            }
            $context->getResponse()->setFormat($format);
            $status  = \loeye\base\Utils::getData($inputs, 'code', 200);
            $context->getResponse()->addOutput($status, 'status');
            $message = \loeye\base\Utils::getData($inputs, 'msg', 'OK');
            if ($message !== null) {
                if (is_array($message)) {
                    $moduleParse = new ModuleParse();
                    foreach ($message as $key => $msg) {
                        $result = $moduleParse->conditionResult($key, $context);
                        if ($result === true) {
                            $msg = $this->printf($msg, $context, $inputs);
                            $context->getResponse()->addOutput($msg, 'message');
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
     * @param \loeye\base\Context $context context
     * @param array               $inputs  inputs
     *
     * @return string
     */
    protected function printf($message, \loeye\base\Context $context, array $inputs)
    {
        $replace = [];
        if (isset($inputs['replace'])) {
            $replace = $inputs['replace'];
        } elseif (isset($inputs['rep_key'])) {
            $replace = \loeye\base\Utils::getData($context, $inputs['rep_key'], null);
        }
        if (!is_array($replace)) {
            Utils::throwError(\loeye\error\BusinessException::INVALID_PARAMETER_MSG, \loeye\error\BusinessException::INVALID_PARAMETER_CODE, \loeye\error\BusinessException::class);
        }
        $translator = $context->get('loeye_translator');
        if (!$translator) {
            $translator = new \loeye\base\Translater($context->getAppConfig());
        }
        return $translator->getString($message, $replace);
    }

}
