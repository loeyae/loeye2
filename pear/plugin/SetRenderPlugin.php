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
 * @version  GIT: $Id: Zhang Yi $
 * @link     https://github.com/loeyae/loeye.git
 */

namespace loeye\plugin;

/**
 * SetRenderPlugin
 *
 * @author   Zhang Yi <loeyae@gmail.com>
 */
class SetRenderPlugin extends \loeye\std\Plugin
{

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
     * @param \loeye\base\Context $context context
     * @param array               $inputs  inputs
     *
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function process(\loeye\base\Context $context, array $inputs)
    {
        $render = \loeye\base\Utils::checkNotEmpty($inputs, 'render');
        $break  = \loeye\base\Utils::getData($inputs, 'break');
        if (isset($inputs['format']) && in_array($inputs['format'], $this->allowedType)) {
            if (isset($inputs['code'])) {
                $context->getResponse()->addOutput($inputs['code'], 'status');
            }
            if (isset($inputs['msg'])) {
                $context->getResponse()->addOutput($inputs['msg'], 'message');
            }
            $context->getResponse()->setFormat($inputs['format']);
        }
        if (is_array($render)) {
            $moduleParse = new ModuleParse();
            foreach ($render as $renderId => $condition) {
                $result = $moduleParse->conditionResult($condition, $context);
                if ($result == true) {
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
            $llt = $context->get('loeye_local_translater');
            if ($llt instanceof LocalTranslater) {
                $errors = array();
                foreach ((array) $inputs['error_key'] as $eKey => $lKey) {
                    $errors[$eKey] = $llt->getString($lKey);
                }
                $context->addErrors('error_tips', $errors);
            } else {
                $context->addErrors('error_tips', $inputs['error_key']);
            }
        }
        if ($break == true) {
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
