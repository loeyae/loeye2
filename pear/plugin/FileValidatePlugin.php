<?php

/**
 * FileValidatePlugin.php
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
 * FileValidatePlugin
 *
 * @author   Zhang Yi <loeyae@gmail.com>
 */
class FileValidatePlugin extends \loeye\std\Plugin
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
        $rule       = \loeye\base\Utils::checkNotEmpty($inputs, 'validate_rule');
        $custBundle = \loeye\base\Utils::getData($inputs, 'bundle', null);
        $validation = new Validation($context->getAppConfig(), $custBundle);
        $report     = $validation->validate($this->_formatFileData(), $rule, $prefix);
        if ($report['has_error'] == true) {
            \loeye\base\Utils::addErrors(
                    $report['error_message'], $context, $inputs, __CLASS__ . '_validate_error');
        }
        \loeye\base\Utils::setContextData(
                $report['valid_data'], $context, $inputs, __CLASS__ . '_filter_data');
    }

    /**
     * _formatFileData
     *
     * @return array
     */
    private function _formatFileData()
    {
        $data = array();
        foreach ($_FILES as $key => $fields) {
            if (is_array($fields['name'])) {
                $data[$key] = $this->_parseData($fields);
            } else if (isset($fields['size']) && $fields['size'] > 0 && !empty($fields['name']) && !empty($fields['tmp_name']) && empty($fields['error'])) {
                $data[$key] = $fields;
            }
        }
        return $data;
    }

    /**
     * _parseData
     *
     * @param array $fields fields
     *
     * @return array
     */
    private function _parseData($fields)
    {
        $data = array();
        foreach ($fields['name'] as $key => $item) {
            if (is_array($item)) {
                $tmpData    = array(
                    'name'     => $item,
                    'tmp_name' => $fields['tmp_name'][$key],
                    'size'     => $fields['size'][$key],
                    'error'    => $fields['error'][$key],
                );
                $data[$key] = $this->_parseData($tmpData);
            } else if (isset($fields['size'][$key]) && $fields['size'][$key] > 0 && !empty($fields['name'][$key]) && !empty($fields['tmp_name'][$key]) && empty($fields['error'][$key])) {
                $data[$key]['name']     = $fields['name'][$key];
                $data[$key]['tmp_name'] = $fields['tmp_name'][$key];
                $data[$key]['size']     = $fields['size'][$key];
                $data[$key]['error']    = $fields['error'][$key];
            }
        }
        return $data;
    }

}
