<?php

/**
 * EsiSwitchPlugin.php
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
 * EsiSwitchPlugin
 *
 * @author   Zhang Yi <loeyae@gmail.com>
 */
class EsiSwitchPlugin extends \loeye\std\Plugin
{

    /**
     * process
     *
     * @param \LOEYE\Context $context context
     * @param array               $inputs  inputs
     *
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function process(\LOEYE\Context $context, array $inputs)
    {
        $context->getResponse()->addHeader('X-ESI', 'ON');
    }

}
