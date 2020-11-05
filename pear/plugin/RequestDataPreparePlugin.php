<?php

/**
 * RequestDataPreparePlugin.php
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
use loeye\std\Plugin;

/**
 * Description of RequestDataPreparePlugin
 *
 * @author   Zhang Yi <loeyae@gmail.com>
 */
class RequestDataPreparePlugin implements Plugin
{
    private $_keyList = 'key_list';

    private $_postOnly = 'post_only';

    private $_dataKey = 'data_key';

    private $_dataIndex = 'index';

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
        $keyList = Utils::checkNotEmpty($inputs, $this->_keyList);
        $keyList = (array)$keyList;

        $postOnly = false;
        if (isset($inputs[$this->_postOnly]) && $inputs[$this->_postOnly] === 'true') {
            $postOnly = true;
        }
        if (empty($inputs[$this->_dataKey])) {
            foreach ($keyList as $key => $value) {
                $outKey = empty($value) ? $key : $value;
                if (filter_has_var(INPUT_POST, $key)) {
                    $context->set($outKey, filter_input(INPUT_POST, $key));
                } else if ($postOnly === true) {
                    $context->set($outKey, null);
                } else {
                    $context->set($outKey, filter_input(INPUT_GET, $key));
                }
            }
        } else {
            $requestData = array();
            foreach ($keyList as $key => $value) {
                $outKey = empty($value) ? $key : $value;
                if (filter_has_var(INPUT_POST, $key)) {
                    $requestData[$outKey] = filter_input(INPUT_POST, $key);
                } else if ($postOnly === true) {
                    $requestData[$outKey] = null;
                } else {
                    $requestData[$outKey] = filter_input(INPUT_GET, $key);
                }
            }
            $data = $context->get($inputs[$this->_dataKey], array());
            if (isset($inputs[$this->_dataIndex])) {
                $data[$inputs[$this->_dataIndex]] = $requestData;
            } else {
                $data = array_merge($data, $requestData);
            }
            $context->set($inputs[$this->_dataKey], $data);
        }
    }

}
