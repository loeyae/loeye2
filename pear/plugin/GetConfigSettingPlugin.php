<?php

/**
 * GetConfigSettingPlugin.php
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
 * GetConfigSettingPlugin
 *
 * @author   Zhang Yi <loeyae@gmail.com>
 */
class GetConfigSettingPlugin extends \loeye\std\ParallelPlugin
{

    private $_bundle = 'bundle';
    private $_context = 'context';
    private $_configKeys = 'config_keys';
    private $_outKeys = 'out_keys';
    private $_config;

    /**
     * prepare
     *
     * @param \loeye\base\ $context context
     * @param array                 $inputs  inputs
     *
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function prepare(\loeye\base\Context $context, array $inputs)
    {
        $bundle        = \loeye\base\Utils::checkNotEmpty($inputs, $this->_bundle);
        $ctx           = \loeye\base\Utils::getData($inputs, $this->_context, null);
        \loeye\base\Utils::checkNotEmpty($inputs, $this->_configKeys);
        $this->_config = new \loeye\base\Configuration(
                $context->getAppConfig()->getPropertyName(), $bundle, $ctx);
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
            $outKeys = $inputs[$this->_outKeys];
        }
        $configKeys = (array) $inputs[$this->_configKeys];
        foreach ($configKeys as $key) {
            $setting = $this->_config->get($key);
            $outKey  = empty($outKeys[$key]) ? __CLASS__ . '_' . $key . '_setting' : $outKeys[$key];
            $context->set($outKey, $setting);
        }
    }

}
