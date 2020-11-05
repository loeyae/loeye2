<?php

/**
 * CheckCrumbPlugin.php
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
use loeye\base\Utils;
use loeye\error\PermissionException;
use loeye\lib\Cookie;
use loeye\std\Plugin;
use Throwable;

/**
 * CheckCrumbPlugin
 *
 * @author   Zhang Yi <loeyae@gmail.com>
 */
class CheckCrumbPlugin implements Plugin
{

    private $_crumbKey = '_crumb';
    private $_settingKey = 'crumb_key';

    /**
     * process
     *
     * @param Context $context context
     * @param array $inputs inputs
     *
     * @return void
     * @throws Throwable
     */
    public function process(Context $context, array $inputs): void
    {
        $crumbKey = Utils::checkNotEmpty($inputs, $this->_settingKey);
        if (isset($inputs['check_crumb']) && $inputs['check_crumb'] === 'true') {
            $crumb = $_REQUEST[$this->_crumbKey] ?? null;
            if (Cookie::validateCrumb($crumbKey, $crumb) === false) {
                if (isset($inputs['output']) && $inputs['output']) {
                    $outputPlugin = new OutputPlugin();
                    $inputsData = ['format' => $inputs['output'],
                        'code' => PermissionException::CRUMB_ERROR_CODE,
                        'msg' => 'crumb check failed'];
                    $outputPlugin->process($context, $inputsData);
                } else {
                    Utils::throwException(
                        'crumb check failed', PermissionException::CRUMB_ERROR_CODE);
                }
            }
        } else {
            $crumb = Cookie::createCrumb($crumbKey);
            Utils::setContextData($crumb, $context, $inputs, __CLASS__ . '_crumb');
        }
    }

}
