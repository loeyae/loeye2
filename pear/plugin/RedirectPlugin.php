<?php

/**
 * HeaderCachePlugin.php
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
use loeye\lib\Cookie;
use loeye\std\Plugin;

/**
 * Description of HeaderCachePlugin
 *
 * @author   Zhang Yi <loeyae@gmail.com>
 */
class RedirectPlugin extends Plugin
{

    public const DEFAULT_CACHE_EXPIRY = 60;

    /**
     * process
     *
     * @param Context $context context
     * @param array $inputs inputs
     *
     * @return mixed
     * @throws Exception
     */
    public function process(Context $context, array $inputs)
    {
        $routerKey = Utils::getData($inputs, 'router_key');
        if (!empty($routerKey)) {
            $parameter = Utils::getData($inputs, 'parameter', array());
            $router    = $context->getRouter();
            $url       = $router->generate($routerKey, $parameter);
        } else {
            $url = Utils::checkNotEmpty($inputs, 'url');
        }
        if (Utils::getData($inputs, 'done')) {
            if (filter_has_var(INPUT_GET, '_done')) {
                $url = rawurldecode(filter_input(INPUT_GET, '_done', FILTER_SANITIZE_URL));
            } else if (filter_has_var(INPUT_COOKIE, '_done')) {
                $url = rawurldecode(Cookie::getCookie('_done'));
                Cookie::destructCookie('_done');
            }
        }
        $response = $context->getResponse();
        $cookie   = $this->getCookie($inputs);
        if ($cookie !== null) {
            $response->addHeader('Set-Cookie', $cookie);
        }
        if (!empty($inputs['header'])) {
            foreach ((array) $inputs['header'] as $key => $value) {
                $response->addHeader($key, $value);
            }
        }
        $response->addHeader('Status', 302);
        $response->setRedirectUrl($url);
        $response->setRenderId(null);
        $response->setHeaders();
        if (isset($inputs['force']) && $inputs['force']) {
            $response->redirect();
        }
        return false;
    }

    /**
     * getCookie
     *
     * @param array $inputs inputs
     *
     * @return mixed
     */
    protected function getCookie(array $inputs)
    {
        if (!empty($inputs['cookie'])) {
            $cookie = (array) $inputs['cookie'];
            array_walk($cookie, static function(&$item, $key) {
                if (!is_numeric($key)) {
                    $item = $key . '=' . $item;
                }
            });
            return implode('&', $cookie);
        }
        return null;
    }

}
