<?php

/**
 * TranslatorPlugin.php
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

/**
 * LocalTranslatorPlugin
 *
 * @category PHP
 * @package  LOEYE
 * @author   Zhang Yi <loeyae@gmail.com>
 * @license  http://www.apache.org/licenses/LICENSE-2.0 Apache License
 * @link     URL description
 */
class TranslatorPlugin extends \loeye\std\Plugin
{

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
        $translator = \loeye\base\Factory::translator($context->getAppConfig());
        $inputs['expire'] = 0;
        \loeye\base\Utils::setContextData($translator, $context, $inputs, 'loeye_translator');
    }

}
