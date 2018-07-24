<?php

/**
 * CookiePlugin.php
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
 * CookiePlugin
 *
 * @author   Zhang Yi <loeyae@gmail.com>
 */
class CookiePlugin extends \loeye\std\Plugin
{

    protected $dataKey = 'set_cookie_data';
    protected $outKey = 'get_cookie_result';

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
        $setKey  = \loeye\base\Utils::getData($inputs, 'set', $this->dataKey);
        $setData = \loeye\base\Utils::getData($context, $setKey);
        if (!empty($setData)) {
            foreach ($setData as $key => $value) {
                if (is_numeric($key)) {
                    continue;
                }
                \loeye\lib\Cookie::setCookie($key, $value);
            }
        }
        $key  = \loeye\base\Utils::getData($inputs, 'get', null);
        $data = array();
        if (empty($key)) {
            $cookie = $context->getRequest()->getCookie();
            if (!empty($cookie)) {
                foreach ($cookie as $key => $value) {
                    $data[$key] = $value;
                }
            }
        } else {
            foreach ((array) $key as $item) {
                $data[$item] = $context->getRequest()->getCookie($item);
            }
        }
        \loeye\base\Utils::setContextData($data, $context, $inputs, $this->outKey);
    }

}
