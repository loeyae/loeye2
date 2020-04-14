<?php

/**
 * ValidatePlugin.php
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
use loeye\error\BusinessException;
use loeye\std\Plugin;
use loeye\validate\Validation;
use loeye\validate\Validator;
use ReflectionException;
use const INPUT_GET;
use const INPUT_POST;
use const INPUT_REQUEST;

/**
 * Description of ValidatePlugin
 *
 * @author   Zhang Yi <loeyae@gmail.com>
 */
class ValidatePlugin extends Plugin
{

    public const INPUT_ORIGIN   = 2;
    public const ENTITY_KEY     = 'entity';
    public const RULE_KEY       = 'validate_rule';
    public const BUNDLE_KEY     = 'bundle';
    public const INPUT_TYPE_KEY = 'type';
    public const GROUPS_KEY     = 'groups';
    public const ERROR_KEY      = 'ValidatePlugin_validate_error';
    public const DATA_KEY       = 'ValidatePlugin_filter_data';

    public static $inputTypes = [
        INPUT_REQUEST,
        self::INPUT_ORIGIN,
        INPUT_POST,
        INPUT_GET,
    ];

    /**
     * process
     *
     * @param Context $context context
     * @param array $inputs inputs
     *
     * @return void
     * @throws ReflectionException
     * @throws Exception
     * @throws BusinessException
     */
    public function process(Context $context, array $inputs): void
    {
        $data = $this->getData($inputs);
        $entity = Utils::getData($inputs, self::ENTITY_KEY);
        if ($entity) {
            $groups = Utils::getData($inputs, self::GROUPS_KEY);
            $entityObject = Utils::source2entity($data, $entity);
            $validator     = Validation::createValidator();
            $violationList = $validator->validate($entityObject, null, $groups);
            $errors        = Validator::buildErrmsg($violationList, Validator::initTranslator($context->getAppConfig()));
            if ($errors) {
                Utils::addErrors($errors, $context, $inputs, self::ERROR_KEY);
            }
            Utils::setContextData($data, $context, $inputs, self::DATA_KEY);
        } else {
            $rule       = Utils::checkNotEmpty($inputs, self::RULE_KEY);
            $customBundle = Utils::getData($inputs, self::BUNDLE_KEY, null);
            $validation = new Validator($context->getAppConfig(), $customBundle);
            $report     = $validation->validate($data, $rule);
            if ($report['has_error']) {
                Utils::addErrors(
                        $report['error_message'], $context, $inputs, self::ERROR_KEY);
            }
            Utils::setContextData(
                    $report['valid_data'], $context, $inputs, self::DATA_KEY);
        }
    }
    
    /**
     * getData
     * 
     * @param array $inputs
     * 
     * @return array
     */
    protected function getData(array $inputs): ?array
    {
        $type = Utils::getData($inputs, self::INPUT_TYPE_KEY, INPUT_REQUEST);
        switch ($type) {
            case INPUT_POST:
                return filter_input_array(INPUT_POST);
            case INPUT_GET:
                return filter_input_array(INPUT_GET);
            case self::INPUT_ORIGIN:
                $data = file_get_contents('php://input');
                return json_decode($data, true);
            default:
                return $_REQUEST;
        }
    }

}
