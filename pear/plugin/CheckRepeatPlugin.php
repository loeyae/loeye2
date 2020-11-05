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
use loeye\base\Context;
use loeye\base\Exception;
use loeye\base\Utils;
use loeye\error\PermissionException;
use loeye\lib\Cookie;
use loeye\std\Plugin;
use Throwable;

/**
 * CheckRepeatPlugin
 *
 * @author   Zhang Yi <loeyae@gmail.com>
 */
class CheckRepeatPlugin implements Plugin
{

    protected $cookieName = '_repeat';

    /**
     * process
     *
     * @param Context $context context
     * @param array $inputs inputs
     *
     * @return mixed
     * @throws Throwable
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function process(Context $context, array $inputs)
    {
        $res   = print_r($_REQUEST, true);
        $name  = md5($res);
        $crumb = Cookie::createCrumb($name);
        $check = Utils::getData($inputs, 'check');
        if ($check === 'true') {
            if (Cookie::getCookie($this->cookieName) === $crumb) {
                $context->set('repeat_submit', true);
                if (Utils::getData($inputs, 'throw_error') == true) {
                    $errmsg = 'repeated request';
                    throw new Exception($errmsg, PermissionException::REPEAT_ERROR_CODE);
                }
                $context->set('page_timeout', true);
                if (Utils::getData($inputs, 'break') == true) {
                    return false;
                }
                if (Utils::getData($inputs, 'redirect') == true) {
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
        if (Utils::getData($inputs, 'clear') == 'true') {
            Cookie::destructCookie($this->cookieName);
        } else {
            Cookie::setCookie($this->cookieName, $crumb);
        }
    }

}
