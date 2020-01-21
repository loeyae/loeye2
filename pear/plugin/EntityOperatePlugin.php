<?php

/**
 * EntityOperatePlugin.php
 *
 * PHP version 7
 *
 * Licensed under the Apache License, Version 2.0 (the "License"),
 * see LICENSE for more details: http://www.apache.org/licenses/LICENSE-2.0.
 *
 * @category PHP
 * @package  LOEYE
 * @author   Zhang Yi <loeyae@gmail.com>
 * @version  2020年1月20日 下午6:48:08
 * @link     https://github.com/loeyae/loeye.git
 */

namespace loeye\plugin;

use loeye\std\plugin;

/**
 * EntityOperatePlugin
 *
 * @author   Zhang Yi <loeyae@gmail.com>
 */
class EntityOperatePlugin extends Plugin
{

    /**
     * process
     *
     * @param \loeye\base\Context $context context
     * @param array               $inputs  inputs
     *
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function process(\loeye\base\Context $context, array $inputs)
    {
        $appConfig = $context->getAppConfig();
        $type      = \loeye\base\Utils::checkNotEmpty($inputs, 'db');
        $entity    = \loeye\base\Utils::checkNotEmpty($inputs, 'entity');
        if (!class_exists($entity)) {
            \loeye\base\Utils::throwException('entity not exists');
        }
        $dbType = $appConfig->getSetting('application.database.'.$type);
        $server = new \loeye\database\Server($appConfig, $dbType);
        $server->setEntity($entity);
        $this->operate($context, $inputs, $server);
    }

    protected function operate(\loeye\base\Context $context, array $inputs, \loeye\database\Server $server)
    {
        $operate = \loeye\base\Utils::checkNotEmpty($inputs, 'operate');
        foreach ((array) $operate as $key => $value) {
            $parameter = isset($value['in']) ? $value['in'] : array();
            $loop      = \loeye\base\Utils::getData($value, 'loop');
            $outKey    = \loeye\base\Utils::checkNotEmpty($value, 'out');
            $errorKey  = \loeye\base\Utils::getData($value, 'error', 'db_operate_error_' . $key);
            try {
                if ($loop == true) {
                    foreach ((array) $parameter as $dkey => $param) {
                        $result[$dkey] = call_user_func_array(array($server, $key), (array) $param);
                    }
                } else {
                    $result[0] = call_user_func_array(array($server, $key), (array) $parameter);
                }
                $result = $this->_filterResult($result);
                $data   = array();
                $errors = array();
                \loeye\base\Utils::filterResultArray($result, $data, $errors);
                if (!empty($errors)) {
                    $context->addErrors($errorKey, $errors);
                }
                $context->set($outKey, $data);
            } catch (\Exception $exc) {
                $context->addErrors($errorKey, $exc);
            }
        }
    }

    /**
     * _filterResult
     *
     * @param array $result result
     *
     * @return LoeyeException
     */
    private function _filterResult(array $result)
    {
        foreach ($result as $key => $value) {
            if (empty($value)) {
                $result[$key] = new \loeye\base\ResourceException(
                \loeye\error\ResourceException::RECORD_NOT_FOUND_MSG, \loeye\error\ResourceException::RECORD_NOT_FOUND_CODE);
            }
        }
        return $result;
    }

}
