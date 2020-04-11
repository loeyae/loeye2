<?php

/**
 * SetRenderPlugin.php
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

use loeye\base\Context;
use loeye\base\Factory;
use loeye\base\Translator;
use loeye\base\Utils;
use loeye\lib\ModuleParse;
use loeye\std\Plugin;
use ReflectionException;
use const loeye\base\RENDER_TYPE_HTML;
use const loeye\base\RENDER_TYPE_JSON;
use const loeye\base\RENDER_TYPE_SEGMENT;
use const loeye\base\RENDER_TYPE_XML;

/**
 * SetRenderPlugin
 *
 * @author   Zhang Yi <loeyae@gmail.com>
 */
class SetRenderPlugin extends Plugin
{
    /**
     * @var array
     */
    private $allowedType;

    public const TRANSLATOR_KEY = 'loeye_translator';

    /**
     * __construct
     *
     * @return void
     */
    public function __construct()
    {

        $this->allowedType = array(
            RENDER_TYPE_SEGMENT,
            RENDER_TYPE_HTML,
            RENDER_TYPE_JSON,
            RENDER_TYPE_XML,
        );
    }

    /**
     * process
     *
     * @param Context $context context
     * @param array $inputs inputs
     *
     * @return mixed|void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @throws ReflectionException
     */
    public function process(Context $context, array $inputs)
    {
        $render = Utils::checkNotEmpty($inputs, 'render');
        $break  = Utils::getData($inputs, 'break');
        if (isset($inputs['format']) && in_array($inputs['format'], $this->allowedType, true)) {
            if (isset($inputs['code'])) {
                $context->getResponse()->addOutput($inputs['code'], 'status');
            }
            if (isset($inputs['msg'])) {
                $context->getResponse()->addOutput($inputs['msg'], 'message');
            }
            $context->getResponse()->setFormat($inputs['format']);
        }
        if (is_array($render)) {
            foreach ($render as $renderId => $condition) {
                $result = ModuleParse::conditionResult($condition, $context);
                if ($result === true) {
                    $context->getResponse()->setRenderId($renderId);
                    break;
                }
            }
        } else {
            $context->getResponse()->setRenderId($render);
        }
        if (isset($inputs['errors'])) {
            foreach ((array) $inputs['errors'] as $key => $error) {
                $context->addErrors($key, $error);
            }
        }
        if (isset($inputs['error_tip'])) {
            $context->addErrors('error_tips', $inputs['error_tip']);
        } else if (isset($inputs['error_key'])) {
            $llt = $context->get(self::TRANSLATOR_KEY);
            if ($llt instanceof Translator) {
                $errors = array();
                foreach ((array) $inputs['error_key'] as $eKey => $lKey) {
                    $errors[$eKey] = $llt->getString($lKey);
                }
                $context->addErrors('error_tips', $errors);
            } else {
                $context->addErrors('error_tips', $inputs['error_key']);
            }
        }
        if ($break === true) {
            return false;
        }
        if (isset($inputs['force']) && $inputs['force']) {
            $render = Factory::getRender($context->getResponse()->getFormat());
            $render->header($context->getResponse());
            $render->output($context->getResponse());
            exit;
        }
    }

}
