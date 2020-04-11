<?php

/**
 * PrivacyCookiePlugin.php
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
use loeye\lib\Cookie;
use loeye\std\Plugin;

/**
 * PrivacyCookiePlugin
 *
 * @author   Zhang Yi <loeyae@gmail.com>
 */
class PrivacyCookiePlugin extends Plugin
{
    protected $dataKey = 'set_loeye_cookie_data';
    protected $outKey = 'get_loeye_cookie_result';
    /**
     * process
     *
     * @param Context $context context
     * @param array $inputs inputs
     *
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function process(Context $context, array $inputs): void
    {
        $setKey = Utils::getData($inputs, 'set', $this->dataKey);
        $setData = Utils::getData($context, $setKey);
        if (!empty($setData)) {
            foreach ($setData as $key => $value) {
                if (is_numeric($key)) {
                    continue;
                }
                Cookie::setLoeyeCookie($key, $value);
            }
        }
        $key = Utils::getData($inputs, 'get', null);
        $data = array();
        if (empty($key)) {
            $cookie = Cookie::getLoeyeCookie() or $cookie = array();
            foreach ($cookie as $key => $value) {
                $data[$key] = $value;
            }
        } else {
            foreach ((array)$key as $item) {
                $data[$item] = Cookie::getLoeyeCookie($item);
            }
        }
        Utils::setContextData($data, $context, $inputs, $this->outKey);
    }

}
