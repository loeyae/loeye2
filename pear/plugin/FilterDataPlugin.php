<?php

/**
 * FilterDataPlugin.php
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
use loeye\base\Exception;
use loeye\base\Utils;
use loeye\error\ResourceException;
use loeye\std\Plugin;
use const loeye\base\PROJECT_SUCCESS;

/**
 * FilterDataPlugin
 *
 * @author   Zhang Yi <loeyae@gmail.com>
 */
class FilterDataPlugin implements Plugin
{

    protected $defaultDataKey = 'filter_data';
    protected $requiredKey = 'required_key';
    protected $optionsKey = 'options_key';
    private $_isList = true;

    /**
     * process
     *
     * @param Context $context context
     * @param array $inputs inputs
     *
     * @return mixed
     */
    public function process(Context $context, array $inputs)
    {
        $result = $this->_setChangeKey($inputs,
            $this->_setSplitKey($context, $inputs, $this->_executeResult($context, $inputs)));
        $this->_setFilterKey($context, $inputs, $result);
        $this->_setPagination($context, $inputs);
        if ($this->_executeError($context, $inputs) === false || $this->_checkRequestKey($context, $inputs) === false) {
            return PROJECT_SUCCESS;
        }
        $required = Utils::getData($inputs, $this->requiredKey, array());
        $options = Utils::getData($inputs, $this->optionsKey, array());
        if (!empty($required) || !empty($options)) {
            $result = Utils::keyFilter($result, $required, $options);
        }
        if (isset($inputs['attach'])) {
            foreach ((array)$inputs['attach'] as $key => $value) {
                $result[$key] = $value;
            }
        }
        Utils::setContextData($result, $context, $inputs, $this->defaultDataKey);
    }

    /**
     * _executeError
     *
     * @param Context $context context
     * @param array $inputs inputs
     *
     * @return boolean
     */
    private function _executeError(Context $context, array $inputs): bool
    {
        if (!isset($inputs['err'])) {
            return true;
        }
        $errors = Utils::getErrors($context, $inputs);
        if (!empty($errors)) {
            if (isset($inputs['throw_error']) && $inputs['throw_error'] == true) {
                if (isset($inputs['ignore_404']) && $inputs['ignore_404'] == true) {
                    foreach ($errors as $error) {
                        if (!$error instanceof \Exception) {
                            $error = new Exception($error);
                        }
                        $code = $error->getCode();
                        if ($code == LOEYE_REST_STATUS_NOT_FOUND || $code == ResourceException::RECORD_NOT_FOUND_CODE) {
                            $context->set('record_not_found', true);
                        } else {
                            Utils::throwError($error);
                        }
                    }
                } else {
                    Utils::throwError(current($errors));
                }
            } else if (isset($inputs['check_error'], $inputs['ignore_404']) && $inputs['check_error'] == true &&
                $inputs['ignore_404'] == true) {
                foreach ($errors as $error) {
                    if (!$error instanceof \Exception) {
                        $error = new Exception($error);
                    }
                    $code = $error->getCode();
                    if ($code == LOEYE_REST_STATUS_NOT_FOUND || $code == ResourceException::RECORD_NOT_FOUND_CODE) {
                        $context->removeErrors($inputs['err']);
                    }
                }
            }
            return false;
        }
        return true;
    }

    /**
     * _checkRequestKey
     *
     * @param Context $context context
     * @param array $inputs inputs
     *
     * @return boolean
     */
    private function _checkRequestKey(Context $context, array $inputs): bool
    {
        $method = null;
        if (isset($inputs['check_method'])) {
            $method = $inputs['check_method'];
            if (mb_strtoupper($method) !== $context->getRequest()->requestMethod) {
                return false;
            }
        }
        if (isset($inputs['check_key'])) {
            $checkKey = $inputs['check_key'];
            if (!is_array($checkKey)) {
                $checkKey = array($checkKey);
            }
            switch ($method) {
                case 'GET':
                    foreach ($checkKey as $key) {
                        if ($context->getRequest()->query->get($key) === null) {
                            return false;
                        }
                    }
                    break;
                case 'POST':
                    foreach ($checkKey as $key) {
                        if ($context->getRequest()->request->get($key) === null) {
                            return false;
                        }
                    }
                    break;
                default :
                    foreach ($checkKey as $key) {
                        if ($context->getRequest()->query->get($key) === null && $context->getRequest()->request->get($key) === null) {
                            return false;
                        }
                    }
                    break;
            }
        }
        return true;
    }

    /**
     * _excuteResult
     *
     * @param Context $context context
     * @param array $inputs inputs
     *
     * @return mixed
     */
    private function _executeResult(Context $context, array $inputs)
    {
        $data = Utils::getContextData($context, $inputs) or $data = [];
        if (!$data) {
            return [];
        }
        if (isset($inputs['only_one'])) {
            if ($inputs['only_one'] === 'true') {
                $result = current($data);
            } else {
                $result = Utils::getData($data, $inputs['only_one']);
            }
        } else {
            $result = $data;
        }
        $this->_isList = $inputs['is_list'] ?? $this->_testResultIsList($result);
        return $result;
    }

    /**
     * _testResultIsList
     *
     * @param mixed $result result
     *
     * @return boolean
     */
    private function _testResultIsList($result): bool
    {
        if (!is_array($result)) {
            return false;
        }
        foreach ($result as $item) {
            if (!is_array($item)) {
                return false;
            }
        }
        return true;
    }

    /**
     * _setChangeKey
     *
     * @param array $inputs inputs
     * @param array $data data
     *
     * @return array
     */
    private function _setChangeKey(array $inputs, $data): array
    {
        if (isset($inputs['change_key']) && is_array($inputs['change_key'])) {
            $changeKey = $inputs['change_key'];
            $intersectKeys = array_intersect_key($changeKey, $data);
            if ($this->_isList && empty($intersectKeys)) {
                foreach ($data as $key => $item) {
                    foreach ($changeKey as $okey => $nkey) {
                        if (array_key_exists($okey, $item)) {
                            $item[$nkey] = $item[$okey];
                            unset($item[$okey]);
                        }
                        $data[$key] = $item;
                    }
                }
            } else {
                foreach ($changeKey as $okey => $nkey) {
                    if (array_key_exists($okey, $data)) {
                        $data[$nkey] = $data[$okey];
                        unset($data[$okey]);
                    }
                }
            }
        }
        return $data;
    }

    /**
     * _setSplitKey
     *
     * @param Context $context context
     * @param array $inputs inputs
     * @param array $data data
     *
     * @return array
     */
    private function _setSplitKey(Context $context, array $inputs, $data): array
    {
        if (isset($inputs['split_key']) && is_array($inputs['split_key'])) {
            $arrLevel = Utils::getArrayLevel($inputs['split_key']);
            if ($arrLevel === 1) {
                $flipKeys = array_flip($inputs['split_key']);
                $intersectKeys = array_intersect_key($flipKeys, $data);
                if ($this->_isList && empty($intersectKeys)) {
                    foreach ($data as $index => $item) {
                        foreach ($inputs['split_key'] as $key) {
                            if (!empty($item[$key]) && is_string($item['key'])) {
                                $item[$key] = split(',', $item[$key]);
                            }
                        }
                        $data[$index] = $item;
                    }
                } else {
                    foreach ($inputs['split_key'] as $key) {
                        if (!empty($data[$key]) && is_string($data['key'])) {
                            $data[$key] = split(',', $data[$key]);
                        }
                    }
                }
            } else {
                $intersectKeys = array_intersect_key($inputs['split_key'], $data);
                if ($this->_isList && empty($intersectKeys)) {
                    foreach ($data as $index => $item) {
                        foreach ($inputs['split_key'] as $key => $setting) {
                            if (!empty($item[$key]) && !is_array($item[$key])) {
                                $pattern = Utils::getData($setting, 'pattern', ',');
                                $item[$key] = split($pattern, $item[$key]);
                                if (!empty($setting['key'])) {
                                    $context->set($setting['key'], $item[$key]);
                                }
                            }
                        }
                        $data[$index] = $item;
                    }
                } else {
                    foreach ($inputs['split_key'] as $key => $setting) {
                        if (!empty($data[$key]) && !is_array($data[$key])) {
                            $pattern = Utils::getData($setting, 'pattern', ',');
                            $data[$key] = explode($pattern, $data[$key]);
                            if (!empty($setting['key'])) {
                                $context->set($setting['key'], $data[$key]);
                            }
                        }
                    }
                }
            }
        }
        return $data;
    }

    /**
     * _setFilterKey
     *
     * @param Context $context context
     * @param array $inputs inputs
     * @param array $data data
     *
     * @return void
     */
    private function _setFilterKey(Context $context, array $inputs, $data): void
    {
        empty($data) && $data = array();
        if (isset($inputs['filter_key']) && is_array($inputs['filter_key'])) {
            if ($this->_isList) {
                foreach ($inputs['filter_key'] as $key => $value) {
                    $result = array();
                    if (is_array($value)) {
                        $ck = key($value);
                        $condition = (array)(current($value));
                        foreach ($data as $index => $item) {
                            if (!is_array($item)) {
                                continue;
                            }
                            if (array_intersect_assoc($condition, $item) === $condition) {
                                $result[$index] = Utils::getData($item, $ck);
                            }
                        }
                    } else if (isset($data[$value])) {
                        $result = $data[$value];
                    } else {
                        foreach ($data as $index => $item) {
                            $result[$index] = Utils::getData($item, $value);
                        }
                    }
                    if (!empty($result)) {
                        $context->set($key, $result);
                    } else {
                        $context->set($key, null);
                    }
                }
            } else {
                foreach ($inputs['filter_key'] as $key => $value) {
                    $context->set($key, Utils::getData($data, $value, null));
                }
            }
        }
    }

    /**
     * _setPagination
     *
     * @param Context $context context
     * @param array $inputs inputs
     *
     * @return void
     */
    private function _setPagination(Context $context, array $inputs): void
    {
        if (isset($inputs['pagination']) && is_array($inputs['pagination'])) {
            $startKey = Utils::checkNotEmpty($inputs['pagination'], 'start');
            $offsetKey = Utils::checkNotEmpty($inputs['pagination'], 'offset');
            $pageKey = Utils::checkNotEmpty($inputs['pagination'], 'page');
            $page = Utils::getData($context, $pageKey) or $page = 1;
            $hits = Utils::checkNotEmpty($inputs['pagination'], 'hits');
            $context->set($startKey, ($page - 1) * $hits);
            $context->set($offsetKey, $hits);
        }
    }

}
