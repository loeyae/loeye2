<?php

/**
 * GetAppConfigPlugin.php
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

use loeye\base\AppConfig;
use loeye\base\Context;
use loeye\base\Utils;
use loeye\std\ParallelPlugin;

/**
 * GetAppConfigPlugin
 *
 * @author   Zhang Yi <loeyae@gmail.com>
 */
class GetAppConfigPlugin extends ParallelPlugin
{

    private $_configKeys = 'config_keys';
    private $_outKeys = 'out_keys';

    /**
     * @var AppConfig
     */
    private $_config;

    /**
     * prepare
     *
     * @param Context $context context
     * @param array $inputs inputs
     *
     * @return void
     */
    public function prepare(Context $context, array $inputs): void
    {
        Utils::checkNotEmpty($inputs, $this->_configKeys);
        $this->_config = $context->getAppConfig();
    }

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
        $outKeys = array();
        if (!empty($inputs[$this->_outKeys])) {
            $outKeys = $inputs[$this->_configKeys];
        }
        $configKeys = (array)$inputs[$this->_configKeys];
        foreach ($configKeys as $key) {
            $setting = $this->_config->getSetting($key);
            $outKey = empty($outKeys[$key]) ? __CLASS__ . '_' . $key . '_setting' : $outKeys[$key];
            $context->set($outKey, $setting);
        }
    }

}
