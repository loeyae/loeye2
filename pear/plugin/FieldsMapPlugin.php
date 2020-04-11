<?php

/**
 * FieldsMapPlugin.php
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
use loeye\base\Utils;
use loeye\error\BusinessException;
use loeye\std\Plugin;
use const loeye\base\PROJECT_SUCCESS;

/**
 * FieldsMapPlugin
 *
 * @author   Zhang Yi <loeyae@gmail.com>
 */
abstract class FieldsMapPlugin extends Plugin
{

    protected $dataKey = 'default_data';
    protected $outKey = 'mapped_data';
    protected $fieldsMap = array();
    protected $operate = array();
    protected $special = array();

    /**
     * process
     *
     * @param Context $context context
     * @param array $inputs inputs
     *
     * @return mixed
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function process(Context $context, array $inputs)
    {
        $flip = Utils::getData($inputs, 'flip', false);
        $this->initMap($flip);
        if (empty($this->fieldsMap)) {
            $errorMsg = '字段对应关系设置无效';
            Utils::throwException(
                $errorMsg, BusinessException::INVALID_PLUGIN_SET_CODE);
        }
        $data = Utils::getContextData($context, $inputs, $this->dataKey);
        if (empty($data)) {
            Utils::setContextData($data, $context, $inputs, $this->outKey);
            return PROJECT_SUCCESS;
        }
        if ($flip) {
            $fields = array_flip($this->fieldsMap);
        } else {
            $fields = $this->fieldsMap;
        }
        $mappedData = array();
        $isList = Utils::getData($inputs, 'list', false);
        if ($isList) {
            foreach ($data as $index => $child) {
                $mappedChild = $this->map($child, $fields);
                if ($mappedChild !== null) {
                    $mappedData[$index] = $mappedChild;
                }
            }
        } else {
            $mappedData = $this->map($data, $fields);
        }
        Utils::setContextData($mappedData, $context, $inputs, $this->outKey);
    }

    /**
     *
     * @param array $data
     * @param array $fields
     *
     * @return array
     */
    protected function map(array $data, array $fields): array
    {
        $mappedData = [];
        if (!empty($fields)) {
            foreach ($fields as $key => $item) {
                if (isset($data[$key])) {
                    $mappedData[$item] = $this->operate($data[$key], $key, $this->operate, $data[$key]);
                } else {
                    $mappedData[$item] = null;
                }
            }
        }
        if (!empty($this->special)) {
            foreach ($this->special as $key) {
                $mappedData[$key] = $this->operate($data, $key, $this->special);
            }
        }
        return $mappedData;
    }

    /**
     * operate
     *
     * @param mixed $data data
     * @param string $key key
     * @param array $operate operate setting
     * @param mixed $default default value
     *
     * @return mixed
     */
    protected function operate($data, $key, array $operate, $default = null)
    {
        if (!isset($operate[$key])) {
            return $default;
        }
        $setting = Utils::checkNotEmpty($operate, $key);
        if (is_string($setting)) {
            $callback = ['callback' => $setting];
        } else if (is_array($setting)) {
            if (isset($setting['method']) && !isset($setting['class'])) {
                if (method_exists($this, $setting['method'])) {
                    $callback = [
                        'class' => get_class($this),
                        'method' => $setting['method'],
                    ];
                    if (isset($setting['param'])) {
                        $callback['param'] = $setting['param'];
                    }
                } else {
                    return $default;
                }
            } else {
                $callback = $setting;
            }
        } else {
            return $default;
        }
        if (empty($callback['param'])) {
            $callback['param'][] = $key;
        } else {
            array_unshift($callback['param'], $key);
        }
        return Utils::callUserFuncArray($data, $callback);
    }

    /**
     * initMap
     * <p>
     * $this->fieldsMap = [
     *                      key1 => alias1,
     *                      key2 => alias2,
     *                      ...
     *                    ]
     * </p>
     * @param bool $flip is flip
     *
     * @return void
     */
    abstract protected function initMap($flip = false): void;
}
