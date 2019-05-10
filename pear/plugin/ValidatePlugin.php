<?php

/**
 * ValidatePlugin.php
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

use loeye\base\{
    Context,
    Utils,
    Validator
};

/**
 * Description of ValidatePlugin
 *
 * @author   Zhang Yi <loeyae@gmail.com>
 */
class ValidatePlugin extends \loeye\std\Plugin
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
        $rule       = \loeye\base\Utils::checkNotEmpty($inputs, 'validate_rule');
        $custBundle = \loeye\base\Utils::getData($inputs, 'bundle', null);
        $validation = new Validator($context->getAppConfig(), $custBundle);
        $report     = $validation->validate($_REQUEST, $rule);
        if ($report['has_error'] == true) {
            \loeye\base\Utils::addErrors(
                    $report['error_message'], $context, $inputs, __CLASS__ . '_validate_error');
        }
        \loeye\base\Utils::setContextData(
                $report['valid_data'], $context, $inputs, __CLASS__ . '_filter_data');
    }

}
