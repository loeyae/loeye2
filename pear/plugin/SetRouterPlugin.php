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
 * @version  2018-07-23 22:44:28
 * @link     https://github.com/loeyae/loeye2.git
 */

namespace loeye\plugin;

use loeye\base\Context;
use loeye\base\Router;
use loeye\error\BusinessException;
use loeye\std\Plugin;
use const loeye\base\PROJECT_SUCCESS;

/**
 * SetRouterPlugin
 *
 * @author   Zhang Yi <loeyae@gmail.com>
 */
class SetRouterPlugin extends Plugin
{

    /**
     * process
     *
     * @param Context $context context
     * @param array $inputs input
     *
     * @return string|void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @throws BusinessException
     */
    public function process(Context $context, array $inputs)
    {
        $router = $context->getRouter();
        if ($router instanceof Router) {
            return PROJECT_SUCCESS;
        }
        $router = new Router($context->getAppConfig()->getPropertyName());
        $context->setRouter($router);
    }

}
