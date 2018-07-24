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
 * @version  GIT: $Id: Zhang Yi $
 * @link     https://github.com/loeyae/loeye.git
 */

namespace loeye\plugin;

/**
 * Description of HeaderCachePlugin
 *
 * @author   Zhang Yi <loeyae@gmail.com>
 */
class HeaderCachePlugin extends \loeye\std\Plugin
{

    const DEFAULT_CACHE_EXPIRY = 60;

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
        $response = $context->getResponse();
        if (array_key_exists('nocache', $inputs) && $inputs['nocache'] == true) {
            $cacheSetting1 = 'no-store, no-cache, must-revalidate';
            $cacheSetting2 = 'post-check=0, pre-check=0';
            $cacheSetting3 = 'private';
            $response->addHeader('Cache-Control', $cacheSetting1);
            $response->addHeader('Cache-Control', $cacheSetting2);
            $response->addHeader('Pragma', 'no-cache');
            $response->addHeader('Cache-Control', $cacheSetting3);
            $response->addHeader('Expires', 0);
        } else {
            $expire = isset($inputs['expire']) ? $inputs['expire'] : self::DEFAULT_CACHE_EXPIRY;
            $response->addHeader('Cache-Control', 'max-age=' . $expire . ', public');
        }
    }

}
