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

use loeye\base\Context;
use loeye\base\Utils;
use loeye\database\Server;
use loeye\error\ResourceException;
use loeye\std\plugin;

/**
 * EntityOperatePlugin
 *
 * @author   Zhang Yi <loeyae@gmail.com>
 */
class EntityOperatePlugin implements Plugin
{

    /**
     * process
     *
     * @param Context $context context
     * @param array $inputs inputs
     *
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function process(Context $context, array $inputs): void
    {
        $appConfig = $context->getAppConfig();
        $type = Utils::checkNotEmpty($inputs, 'db');
        $entity = Utils::checkNotEmpty($inputs, 'entity');
        if (!class_exists($entity)) {
            Utils::throwException('entity not exists');
        }
        $dbType = $appConfig->getSetting('application.database.' . $type) ?? $type;
        $server = new Server($appConfig, $dbType);
        $server->setEntity($entity);
        $this->operate($context, $inputs, $server);
    }

    /**
     * operate
     *
     * @param Context $context
     * @param array $inputs
     * @param Server $server
     *
     * @return void
     */
    protected function operate(Context $context, array $inputs, Server $server): void
    {
        $operate = Utils::checkNotEmpty($inputs, 'operate');
        foreach ((array)$operate as $key => $value) {
            $parameter = $value['in'] ?? array();
            $loop = Utils::getData($value, 'loop');
            $outKey = Utils::checkNotEmpty($value, 'out');
            $errorKey = Utils::getData($value, 'error', 'db_operate_error_' . $key);
            try {
                $result = [];
                if ($loop == true) {
                    foreach ((array)$parameter as $dkey => $param) {
                        $result[$dkey] = call_user_func_array(array($server, $key), [$param]);
                    }
                } else {
                    $result[0] = call_user_func_array(array($server, $key), [$parameter]);
                }
                $result = $this->_filterResult($result);
                $data = array();
                $errors = array();
                Utils::filterResultArray($result, $data, $errors);
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
     * @param array $result
     * @return array
     */
    private function _filterResult(array $result): array
    {
        foreach ($result as $key => $value) {
            if (empty($value)) {
                $result[$key] = new ResourceException(
                    ResourceException::RECORD_NOT_FOUND_MSG, ResourceException::RECORD_NOT_FOUND_CODE);
            }
        }
        return $result;
    }

}
