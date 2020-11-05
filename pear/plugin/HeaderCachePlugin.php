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
use loeye\std\Plugin;

/**
 * Description of HeaderCachePlugin
 *
 * @author   Zhang Yi <loeyae@gmail.com>
 */
class HeaderCachePlugin implements Plugin
{

    public const DEFAULT_CACHE_EXPIRY = 60;

    /**
     * process
     *
     * @param Context $context context
     * @param array $inputs inputs
     *
     * @return void
     */
    public function process(Context $context, array $inputs): void
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
            $expire = $inputs['expire'] ?? self::DEFAULT_CACHE_EXPIRY;
            $response->addHeader('Cache-Control', 'max-age=' . $expire . ', public');
        }
    }

}
