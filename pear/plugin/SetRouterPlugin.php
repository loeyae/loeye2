<?php

/**
 * SetRouterPlugin.php
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
 * SetRouterPlugin
 *
 * @author   Zhang Yi <loeyae@gmail.com>
 */
class SetRouterPlugin extends \loeye\std\Plugin
{

    /**
     * process
     *
     * @param \loeye\base\Context $context context
     * @param array               $inputs  input
     *
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function process(\loeye\base\Context $context, array $inputs)
    {
        $router = $context->getRouter();
        if ($router instanceof Router) {
            return PROJECT_SUCCESS;
        }
        $router = new Router(
            PROJECT_ROUTER_DIR, $context->getAppConfig()->getPropertyName());
        $context->setRouter($router);
    }

}
