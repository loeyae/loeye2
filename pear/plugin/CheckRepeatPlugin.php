<?php

/**
 * CheckRepeatPlugin.php
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
 * CheckRepeatPlugin
 *
 * @author   Zhang Yi <loeyae@gmail.com>
 */
class CheckRepeatPlugin extends \loeye\std\Plugin
{

    protected $cookieName = '_repeat';

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
        $res   = print_r($_REQUEST, true);
        $name  = md5($res);
        $crumb = \loeye\lib\Cookie::createCrumb($name);
        $check = \loeye\base\Utils::getData($inputs, 'check');
        if ($check == 'true') {
            if (Cookie::getCookie($this->cookieName) == $crumb) {
                $context->set('repeat_submit', true);
                if (\loeye\base\Utils::getData($inputs, 'throw_error') == true) {
                    $errmsg = 'repeated request';
                    throw new \loeye\base\Exception($errmsg, PermissionException::REPEAT_ERROR_CODE);
                }
                $context->set('page_timeout', true);
                if (\loeye\base\Utils::getData($inputs, 'break') == true) {
                    return false;
                }
                if (\loeye\base\Utils::getData($inputs, 'redirect') == true) {
                    $redirectPlugin = new RedirectPlugin();
                    $redirectPlugin->process($context, $inputs['redirect']);
                }
                if (isset($inputs['output']) && $inputs['output']) {
                    $outputPlugin = new OutputPlugin();
                    $inputsData   = ['format' => $inputs['output'],
                        'code'   => PermissionException::REPEAT_ERROR_CODE,
                        'msg'    => 'repeated request'];
                    $outputPlugin->process($context, $inputsData);
                }
            }
        }
        if (\loeye\base\Utils::getData($inputs, 'clear') == 'true') {
            Cookie::destructCookie($this->cookieName);
        } else {
            Cookie::setCookie($this->cookieName, $crumb);
        }
    }

}
