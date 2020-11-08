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

use loeye\base\Context;
use loeye\base\Exception;
use loeye\base\Utils;
use loeye\validate\Validator;
use loeye\error\BusinessException;
use loeye\error\ValidateError;
use loeye\std\Plugin;
use loeye\validate\Validation;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * FileValidatePlugin
 *
 * @author   Zhang Yi <loeyae@gmail.com>
 */
class FileValidatePlugin implements Plugin
{
    public const ENTITY_KEY     = 'entity';
    public const RULE_KEY       = 'validate_rule';
    public const BUNDLE_KEY     = 'bundle';
    public const GROUPS_KEY     = 'groups';
    public const ERROR_KEY      = 'FileValidatePlugin_validate_error';
    public const DATA_KEY       = 'FileValidatePlugin_filter_data';

    /**
     * process
     *
     * @param Context $context context
     * @param array $inputs inputs
     *
     * @return void
     * @throws \ReflectionException
     * @throws Exception
     * @throws BusinessException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function process(Context $context, array $inputs): void
    {
        $data = $this->_formatFileData();
        $entity = Utils::getData($inputs, self::ENTITY_KEY);
        if ($entity) {
            $groups = Utils::getData($inputs, self::GROUPS_KEY);
            $entityObject = Utils::source2entity($data, $entity);
            $validator     = Validation::createValidator();
            $violationList = $validator->validate($entityObject, null, $groups);
            $errors        = Validator::buildErrmsg($violationList, Validator::initTranslator($context->getAppConfig()));
            if ($errors) {
                Utils::addErrors(new ValidateError($errors), $context, $inputs, self::ERROR_KEY);
            }
            Utils::setContextData($data, $context, $inputs, self::DATA_KEY);
        } else {
            $rule = Utils::checkNotEmpty($inputs, self::RULE_KEY);
            $customBundle = Utils::getData($inputs, self::BUNDLE_KEY, null);
            $validation = new Validator($context->getAppConfig(), $customBundle);
            $report = $validation->validate($data, $rule);
            if ($report['has_error'] == true) {
                Utils::addErrors(
                    new ValidateError($report['error_message']), $context, $inputs, self::ERROR_KEY);
            }
            Utils::setContextData(
                $report['valid_data'], $context, $inputs, self::DATA_KEY);
        }
    }

    /**
     * _formatFileData
     *
     * @return array
     */
    private function _formatFileData(): array
    {
        $data = array();
        foreach ($_FILES as $key => $fields) {
            if (is_array($fields['name'])) {
                $data[$key] = $this->_parseData($fields);
            } else if (isset($fields['size']) && $fields['size'] > 0 && !empty($fields['name']) && !empty($fields['tmp_name']) && empty($fields['error'])) {
                $data[$key] = new UploadedFile($fields['tmp_name'], $fields['name'], $fields['error']);
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
    private function _parseData($fields): array
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
                $data[$key] = new UploadedFile($fields['tmp_name'][$key], $fields['name'][$key], $fields['error'][$key]);
            }
        }
        return $data;
    }

}
