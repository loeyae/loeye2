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
 * @version  GIT: $Id: Zhang Yi $
 * @link     https://github.com/loeyae/loeye.git
 */

namespace loeye\plugin;

/**
 * GetAppConfigPlugin
 *
 * @author   Zhang Yi <loeyae@gmail.com>
 */
class GetAppConfigPlugin extends \loeye\std\ParallelPlugin
{

    private $_configKeys = 'config_keys';
    private $_outKeys = 'out_keys';
    private $_config;

    /**
     * prepare
     *
     * @param \loeye\base\Context $context context
     * @param array               $inputs  inputs
     *
     * @return void
     */
    public function prepare(\loeye\base\Context $context, array $inputs)
    {
        \loeye\base\Utils::checkNotEmpty($inputs, $this->_configKeys);
        $this->_config = $context->getAppConfig();
    }

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
        $outKeys = array();
        if (!empty($inputs[$this->_outKeys])) {
            $outKeys = $inputs[$this->_configKeys];
        }
        $configKeys = (array) $inputs[$this->_configKeys];
        foreach ($configKeys as $key) {
            $setting = $this->_config->getSetting($key);
            $outKey  = empty($outKeys[$key]) ? __CLASS__ . '_' . $key . '_setting' : $outKeys[$key];
            $context->set($outKey, $setting);
        }
    }

}
