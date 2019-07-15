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
use loeye\error\PermissionException;

/**
 * CheckCrumbPlugin
 *
 * @author   Zhang Yi <loeyae@gmail.com>
 */
class CheckCrumbPlugin extends \loeye\std\Plugin
{

    private $_crumbKey = '_crumb';
    private $_settingKey = 'crumb_key';

    /**
     * process
     *
     * @param \loeye\base\Context $context context
     * @param array               $inputs  inputs
     *
     * @return void
     */
    public function process(\loeye\base\Context $context, array $inputs)
    {
        $crumbKey = \loeye\base\Utils::checkNotEmpty($inputs, $this->_settingKey);
        if (isset($inputs['check_crumb']) && $inputs['check_crumb'] == 'true') {
            $crumb = null;
            if (isset($_REQUEST[$this->_crumbKey])) {
                $crumb = $_REQUEST[$this->_crumbKey];
            }
            if (Cookie::validateCrumb($crumbKey, $crumb) == false) {
                if (isset($inputs['output']) && $inputs['output']) {
                    $outputPlugin = new OutputPlugin();
                    $inputsData   = ['format' => $inputs['output'],
                        'code'   => PermissionException::CRUMB_ERROR_CODE,
                        'msg'    => 'crumb验证失败'];
                    $outputPlugin->process($context, $inputsData);
                } else {
                    \loeye\base\Utils::throwException(
                            'crumb验证失败', \PermissionException::CRUMB_ERROR_CODE);
                }
            }
        } else {
            $crumb = Cookie::createCrumb($crumbKey);
            \loeye\base\Utils::setContextData($crumb, $context, $inputs, __CLASS__ . '_crumb');
        }
    }

}
