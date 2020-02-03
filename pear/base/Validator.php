<?php

/**
 * Validator.php
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

namespace loeye\base;

use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Translation as I18n;
use loeye\error\BusinessException;
use Symfony\Component\Validator\Context\ExecutionContext;

/**
 * Description of Validator
 *
 * @author   Zhang Yi <loeyae@gmail.com>
 */
class Validator {

    use \loeye\std\ConfigTrait;

    private $_report;

    /**
     *
     * @var \loeye\base\AppConfig
     */
    protected $appConfig;

    /**
     *
     * @var \loeye\base\Configuration
     */
    protected $config;

    /**
     *
     * @var Symfony\Component\Translation\Translator
     */
    protected $translater;

    const LLT_PREFIX = 'LV_FIELD_';
    const KEYWORD = 'validate';
    const BUNDLE = 'validate';

    protected $prefix;

    /**
     *
     * @param \loeye\base\AppConfig       $appConfig        AppConfig instance
     * @param string|null                 $bundle           bundle
     * @param array                       $validationConfig validation config setting
     */
    public function __construct(AppConfig $appConfig, $bundle = null, $validationConfig = array()
    ) {
        $this->appConfig = $appConfig;
        $definition  = [new \loeye\config\validate\RulesetConfigDefinition(), new \loeye\config\validate\DeltaConfigDefinition()];
        $this->config = $this->bundleConfig($appConfig->getPropertyName(), $bundle, $definition);
        $this->_report = array('has_error' => false, 'error_message' => []);
        $this->_initTranslater($appConfig);
    }

    /**
     * validate
     *
     * @param array  $data   data
     * @param string $rule   rule
     *
     * @return array()
     */
    public function validate($data, $rule) {
        $rulesets = $this->config->get('rulesets');
        $validateSets = $this->config->getConfig(null, self::KEYWORD . '=' . $rule);
        if (empty($validateSets[self::KEYWORD]) || empty($validateSets[self::KEYWORD]['fields'])) {
            throw new BusinessException(
                    BusinessException::INVALID_CONFIG_SET_MSG, BusinessException::INVALID_CONFIG_SET_CODE);
        }
        $schema = $this->_initSchema($validateSets[self::KEYWORD]['fields'], $data);
        $fdata = $this->_filterData($rulesets, $schema, $data);
        $validator = Validation::createValidator();
        $constraintList = $this->_buildConstraintList($rulesets, $schema, $data);
        $violationList = new ConstraintViolationList();
        if ($constraintList) {
            $constraint = new Assert\Collection([
                'fields' => $constraintList,
                'allowExtraFields' => true,
            ]);
            $violationList = $validator->validate($data, $constraint);
        }
        $validData = $this->_filter($fdata, $violationList);
        if (!empty($this->_report['error_message'])) {
            $this->_report['has_error'] = true;
        }
        $this->_report['valid_data'] = $validData;
        return $this->_report;
    }

    private function _buildConstraintList($rulesets, $schema, $data) {
        $validationList = [];
        $requiredFields = [];
        foreach ($schema as $key => $rule) {
            $validator = $this->_buildConstraint($rulesets, $rule);
            if ($validator) {
                $validationList[$key] = $validator;
            }
            $requiredFields = array_replace($requiredFields, $this->_getRequiredFields($key, $rule, $data));
        }
        foreach ($validationList as $key => $constraintList) {
            if (array_key_exists($key, $requiredFields)) {
                if ($requiredFields[$key] == 'required_value') {
                    array_unshift($constraintList, new Assert\NotBlank());
                    array_unshift($constraintList, new Assert\NotNull());
                }
                $validator = new Assert\Required($constraintList);
            } else {
                $validator = new Assert\Optional($constraintList);
            }
            $validationList[$key] = $validator;
        }
        return $validationList;
    }

    private function _getRequiredFields($key, $rule, $data) {
        $requiredFields = [];
        if (isset($rule['required_value'])) {
            $requiredFields = [$key => 'required_value'];
        }
        if (isset($rule['required_key'])) {
            $requiredFields = [$key => 'required_value'];
        }
        if (isset($rule['required_value_if_match'])) {
            foreach ($rule['required_value_if_match'] as $v => $f) {
                if ($data[$key] == $v) {
                    $requiredFields = array_replace($requiredFields, array_fill_keys((array) $f, 'required_value'));
                }
            }
        }
        if (isset($rule['required_value_if_include'])) {
            foreach ($rule['required_value_if_include'] as $v => $f) {
                if (in_array($v, $data[$key])) {
                    $requiredFields = array_replace($requiredFields, array_fill_keys((array) $f, 'required_value'));
                }
            }
        }
        if (isset($rule['required_value_if_key_exists'])) {
            if (isset($data[$key])) {
                $requiredFields = [$key => 'required_value'];
            }
        }
        if (isset($rule['required_value_if_blank'])) {
            if (isset($data[$key]) && $data[$key] == '') {
                $requiredFields = array_replace($requiredFields, array_fill_keys((array) $rule['required_value_if_blank'], 'required_value'));
            }
        }
        return $requiredFields;
    }

    /**
     *
     * @param type $rulesets
     * @param type $setting
     * @return \Symfony\Component\Validator\Constraints\All
     * @throws BusinessException
     */
    private function _buildConstraint($rulesets, $setting) {
        $ruleset = $rulesets[$setting['rule']] ?? null;
        if (!$ruleset) {
            throw new BusinessException(BusinessException::INVALID_CONFIG_SET_MSG, BusinessException::INVALID_CONFIG_SET_CODE, ['setting' => 'validate ruleset: '.$setting['rule']]);
        }
        $ruleset = array_filter($ruleset, function($item){
            if (null === $item) {
                return false;
            }
            if ([] === $item) {
                return false;
            }
            if (false === $item) {
                return false;
            }
            if ('' === $item) {
                return false;
            }
            return $item;
        });
        $funConstraint = $this->_buildCallbackConstraint($ruleset);
        $typeConstraint = $this->_buildTypeConstraint($ruleset);
        $lengthConstraint = $this->_buildLengthConstraint($ruleset);
        $countConstraint = $this->_buildCountConstraint($ruleset);
        $rangeConstraint = $this->_buildRangeConstraint($ruleset);
        $regexConstraint = $this->_buildRegexConstraint($ruleset);
        $choiceConstraint = $this->_buildChoiceConstraint($ruleset);
        $operatorConstraint = $this->_buildOperatorConstraint($ruleset);
        $validatorList = array_merge($funConstraint, $typeConstraint,
                $lengthConstraint, $countConstraint, $rangeConstraint,
                $regexConstraint, $choiceConstraint, $operatorConstraint);
        if (isset($setting['item'])) {
            $allConstraintor = new Assert\All($this->_buildConstraint($rulesets, $setting['item']));
            $validatorList[] = $allConstraintor;
        }
        return $validatorList;
    }

    private function _buildLengthConstraint($ruleset) {
        $lengthKeys = ['length', 'min_length', 'max_length'];
        if (array_intersect_key($ruleset, array_fill_keys($lengthKeys, null))) {
            $options = [
                'min' => (isset($ruleset['min_length']) ? $ruleset['min_length'] : (
                    isset($ruleset['length'][0]) ? $ruleset['length'][0] : (
                    isset($ruleset['length']) ? $ruleset['length'] : null))),
                'max' => (isset($ruleset['max_length']) ? $ruleset['max_length'] : (
                    isset($ruleset['length'][1]) ? $ruleset['length'][1] : (
                    isset($ruleset['length']) ? $ruleset['length'] : null))),
            ];
            $validator = new Assert\Length($options);
            !isset($ruleset['length_errmsg']) ?: $validator->exactMessage = $ruleset['length_errmsg'];
            !isset($ruleset['min_length_errmsg']) ?: $validator->minMessage = $ruleset['min_length_errmsg'];
            !isset($ruleset['max_length_errmsg']) ?: $validator->maxMessage = $ruleset['max_length_errmsg'];
            return [$validator];
        }
        return [];
    }

    private function _buildCountConstraint($ruleset) {
        $countKeys = ['count', 'min_count', 'max_count'];
        if (array_intersect_key($ruleset, array_fill_keys($countKeys, null))) {
            $options = [
                'min' => (isset($ruleset['min_count']) ? $ruleset['min_count'] : (
                    isset($ruleset['count'][0]) ? $ruleset['count'][0] : (
                    isset($ruleset['count']) ? $ruleset['count'] : null))),
                'max' => (isset($ruleset['max_count']) ? $ruleset['max_count'] : (
                    isset($ruleset['count'][1]) ? $ruleset['count'][1] : (
                    isset($ruleset['count']) ? $ruleset['count'] : null))),
            ];
            $validator = new Assert\Count($options);
            !isset($ruleset['count_errmsg']) ?: $validator->exactMessage = $ruleset['count_errmsg'];
            !isset($ruleset['min_count_errmsg']) ?: $validator->minMessage = $ruleset['min_count_errmsg'];
            !isset($ruleset['max_count_errmsg']) ?: $validator->maxMessage = $ruleset['max_count_errmsg'];
            return [$validator];
        }
        return [];
    }

    private function _buildRangeConstraint($ruleset) {
        $rangeKeys = ['range', 'min', 'max'];
        if (array_intersect_key($ruleset, array_fill_keys($rangeKeys, null))) {
            $options = [
                'min' => (isset($ruleset['min']) ? $ruleset['min'] : (
                    isset($ruleset['range'][0]) ? $ruleset['range'][0] : (
                    isset($ruleset['range']) ? $ruleset['range'] : null
                ))),
                'max' => (isset($ruleset['max']) ? $ruleset['max'] : (
                    isset($ruleset['range'][1]) ? $ruleset['range'][1] : (
                    isset($ruleset['range']) ? $ruleset['range'] : null
                ))),
            ];
            $validator = new Assert\Range($options);
            !isset($ruleset['min_errmsg']) ?: $validator->minMessage = $ruleset['min_errmsg'];
            !isset($ruleset['max_errmsg']) ?: $validator->maxMessage = $ruleset['max_errmsg'];
            return [$validator];
        }
        return [];
    }

    private function _buildRegexConstraint($ruleset) {
        if (isset($ruleset['regex']) && $ruleset['regex']) {
            $options = [
                'pattern' => isset($ruleset['regex']['pattern']) ? $ruleset['regex']['pattern'] : null,
                'htmlPattern' => isset($ruleset['regex']['html_pattern']) ? $ruleset['regex']['html_pattern'] : null,
                'match' => isset($ruleset['regex']['match']) ? $ruleset['regex']['match'] : true,
            ];
            $validator = new Assert\Regex($options);
            return [$validator];
        }
        return [];
    }

    private function _buildChoiceConstraint($ruleset) {
        if (isset($ruleset['choice']) && $ruleset['choice']) {
            $options = [
                'choices' => $ruleset['choice'],
                'callback' => isset($ruleset['choice_callback']) ? $ruleset['choice_callback'] : null,
                'multiple' => isset($ruleset['choice_multiple']) ? $ruleset['choice_multiple'] : false,
                'min' => isset($ruleset['choice_min']) ? $ruleset['choice_min'] : null,
                'max' => isset($ruleset['choice_max']) ? $ruleset['choice_max'] : null,
            ];
            $validator = new Assert\Choice($options);
            !isset($ruleset['choice_errmsg']) ?: $validator->message = $ruleset['choice_errmsg'];
            !isset($ruleset['choice_multiple_errmsg']) ?: $validator->multipleMessage = $ruleset['choice_multiple_errmsg'];
            !isset($ruleset['choice_min_errmsg']) ?: $validator->minMessage = $ruleset['choice_min_errmsg'];
            !isset($ruleset['choice_max_errmsg']) ?: $validator->maxMessage = $ruleset['choice_max_errmsg'];
            return [$validator];
        }
        return [];
    }

    private function _buildOperatorConstraint($ruleset) {
        $validatorList = [];
        $operatorMapping = [
            '=' => 'EqualTo',
            '==' => 'IdenticalTo',
            '>' => 'LessThan',
            '>=' => 'LessThanOrEqual',
            '<' => 'GreaterThan',
            '<=' => 'GreaterThanOrEqual'
        ];
        $intersect = array_intersect_key($ruleset, $operatorMapping);
        if ($intersect) {
            foreach ($intersect as $key => $value) {
                $item = $operatorMapping[$key];
                $rc = \ReflectionClass('\\Symfony\\Component\\Validator\\Constraints\\' . ucfirst($item));
                $validator = $rc->newInstance(['value' => $value]);
                !$ruleset[$key . '_errmsg'] ?: $validator->message = $ruleset[$key . '_errmsg'];
                $validatorList[] = $validator;
            }
        }
        return $validatorList;
    }

    private function _buildTypeConstraint($ruleset) {
        $type = $ruleset['type'] ?? null;
        $message = $ruleset['type_errmsg'] ?? null;
        $validator = null;
        if ($type && function_exists('\\is_' . $type)) {
            $validator = new Assert\Type(['type' => $type]);
        } else if ($type && class_exists('\\Symfony\\Component\\Validator\\Constraints\\' . ucfirst($type))) {
            $rc = new \ReflectionClass('\\Symfony\\Component\\Validator\\Constraints\\' . ucfirst($type));
            $validator = $rc->newInstanceArgs();
            if ($type == 'image') {
                $validator->mimeTypes = (isset($ruleset['mim-types']) ? $ruleset['mim-types'] : 'image/*');
                !isset($ruleset['mim-types_errmsg']) ?: $validator->mimeTypesMessage = $ruleset['mim-types_errmsg'];
                $validator->minWidth = isset($ruleset['min_width']) ? $ruleset['min_width'] : null;
                !isset($ruleset['min_width_errmsg']) ?: $validator->minWidthMessage = $ruleset['min_width_errmsg'];
                $validator->maxWidth = (isset($ruleset['max_width']) ? $ruleset['max_width'] : null);
                !isset($ruleset['max_width_errmsg']) ?: $validator->maxWidthMessage = $ruleset['mmax_width_errmsg'];
                $validator->maxHeight = (isset($ruleset['max_height']) ? $ruleset['max_height'] : null);
                !isset($ruleset['max_height_errmsg']) ?: $validator->maxHeightMessage = $ruleset['max_height_errmsg'];
                $validator->minHeight = (isset($ruleset['min_height']) ? $ruleset['min_height'] : null);
                !isset($ruleset['min_height_errmsg']) ?: $validator->minHeightMessage = $ruleset['min_height_errmsg'];
                $validator->maxRatio = (isset($ruleset['max_ratio']) ? $ruleset['max_ratio'] : null);
                !isset($ruleset['max_ratio_errmsg']) ?: $validator->maxRatioMessage = $ruleset['max_ratio_errmsg'];
                $validator->minRatio = (isset($ruleset['min_ratio']) ? $ruleset['min_ratio'] : null);
                !isset($ruleset['min_ratio_errmsg']) ?: $validator->minRatioMessage = $ruleset['min_ratio_errmsg'];
                $validator->minPixels = (isset($ruleset['min_pixels']) ? $ruleset['min_pixels'] : null);
                !isset($ruleset['min_pixels_errmsg']) ?: $validator->minPixelsMessage = $ruleset['min_pixels_errmsg'];
                $validator->maxPixels = (isset($ruleset['max_pixels']) ? $ruleset['max_pixels'] : null);
                !isset($ruleset['max_pixels_errmsg']) ?: $validator->maxPixelsMessage = $ruleset['max_pixels_errmsg'];
                $validator->maxSize = (isset($ruleset['max_size']) ? $ruleset['max_size'] : null);
                !isset($ruleset['max_size_errmsg']) ?: $validator->maxSizeMessage = $ruleset['max_size_errmsg'];
            } else if ($type == 'file') {
                $validator->mimeTypes = (isset($ruleset['mim-types']) ? $ruleset['mim-types'] : []);
                !isset($ruleset['mim-types_errmsg']) ?: $validator->mimeTypesMessage = $ruleset['mim-types_errmsg'];
                $validator->maxSize = (isset($ruleset['max_size']) ? $ruleset['max_size'] : null);
                !isset($ruleset['max_size_errmsg']) ?: $validator->maxSizeMessage = $ruleset['max_size_errmsg'];
            } else if ($type == 'ip') {
                $validator->version = (isset($ruleset['version']) ? $ruleset['version'] : Assert\Ip::V4);
            }
        } else if ($type) {
            throw new BusinessException('No Support type: %type%', BusinessException::DEFAULT_ERROR_CODE, ['type' => $type]);
        }
        (!$message && $validator) ?: $validator->message = $message;
        return $validator ? [$validator] : [];
    }

    /**
     * _buildCallbackConstraint
     *
     * @param type $ruleset
     * @return array
     */
    private function _buildCallbackConstraint($ruleset) {
        $fun = isset($ruleset['callback']) ? $ruleset['callback'] : null;
        $msg = isset($ruleset['callback_message']) ? $ruleset['callback_message'] : null;
        $validatorList = [];
        if ($fun) {
            if (is_iterable($fun)) {
                foreach ($fun as $item) {
                    $callback = $item['name'];
                    $message = isset($item['message']) ? $item['message'] : $msg;
                    $validatorList[] = $this->_buidCallbackValidator($callback, $message);
                }
            }
        }
        return $validatorList;
    }

    /**
     * _buidCallbackValidator
     *
     * @param type $callback
     * @param type $message
     * @return \Symfony\Component\Validator\Constraints\Callback
     */
    private function _buidCallbackValidator(callable $callback, $message) {
        $appConfig = $this->appConfig;
        $f = function($object, ExecutionContext $context, $payload) use ($appConfig, $callback, $message) {
            if (!call_user_func_array($callback, [$object, $appConfig])) {
                $context->buildViolation($message)
                        ->setParameter('{{ value }}', $object)
                        ->setCode(uniqid())
                        ->addViolation();
            }
        };
        $options = [
            "callback" => $f
        ];
        return new Assert\Callback($options);
    }

    /**
     * _filterData
     * @param type $rulesets
     * @param type $schema
     * @param type $data
     * @return type
     * @throws BusinessException
     */
    private function _filterData($rulesets, $schema, $data) {
        $filtedData = [];
        foreach ($data as $key => $value) {
            if (isset($schema[$key])) {
                $ruleset = $rulesets[$schema[$key]['rule']] ?? null;
                if (!$ruleset) {
                    throw new BusinessException(BusinessException::INVALID_CONFIG_SET_MSG, BusinessException::INVALID_CONFIG_SET_CODE, ['setting' => 'validate ruleset: '. $schema[$key]['rule']]);
                }
                if (!empty($ruleset['filter'])) {
                    $filtedData[$key] = $this->_filterVar($value, $ruleset);
                    continue;
                }
            }
            $filtedData[$key] = filter_var($value, FILTER_SANITIZE_FULL_SPECIAL_CHARS, FILTER_FLAG_NO_ENCODE_QUOTES);
        }
        return $filtedData;
    }

    /**
     * _filterVar
     *
     * @param type $data
     * @param type $ruleset
     * @return type
     */
    private function _filterVar($data, $ruleset) {
        if (is_iterable($data)) {
            $filted = [];
            foreach ($data as $key => $value) {
                $filted[$key] = $this->_filterVar($value, $ruleset);
            }
            return $filted;
        }
        $filter = isset($ruleset["filter"]["filter_type"]) ? constant($ruleset["filter"]["filter_type"]) : FILTER_SANITIZE_FULL_SPECIAL_CHARS;
        $ops = [];
        if (!empty($ruleset["filter"]["options"])) {
            if (is_iterable($ruleset["filter"]["options"])) {
                $ops = $ruleset["filter"]["options"];
            } else {
                $ops['flag'] = $ruleset["filter"]["options"];
            }
        }
        !isset($ruleset["filter"]["filter_flag"]) ?: $ops['flag'] = constant($ruleset["filter"]['filter_flag']);
        !isset($ruleset["filter"]['filter_options']) ?: $ops['options'] = $ruleset["filter"]['filter_options'];
        $validated = filter_var($data, $filter, $ops);
        if ($validated !== false) {
            if (!empty($ruleset['fun'])) {
                foreach ($ruleset['fun'] as $funset) {
                    $fun = $funset['name'];
                    if(is_callable($fun)) {
                        $params = (array )($funset['params'] ?? []);
                        array_unshift($params, $validated);
                        $validated = call_user_func_array($fun, $params);
                    }
                }
            }
        }
        return $validated;
    }

    /**
     * _initSchema
     *
     * @param array $schema rule name
     * @param array $data   data
     *
     * @return array
     * @throws Exception
     */
    private function _initSchema($schema, $data) {
        foreach ($schema as $key => $rule) {
            if (isset($rule['fields'])) {
                $children = $rule['fields'];
                foreach ($children as $k => $child) {
                    if (empty($child) || !is_array($child)) {
                        throw new BusinessException(
                                BusinessException::INVALID_CONFIG_SET_MSG, BusinessException::INVALID_CONFIG_SET_CODE, ['setting' => 'validate ruleset child']);
                    }
                    $childrenSchema = array();
                    if ($k == 'i') {
                        if (isset($data[$key]) && is_array($data[$key])) {
                            foreach ($data[$key] as $i => $value) {
                                if (isset($child['fields'])) {
                                    $child['children'] = $this->_initSchema($child, $value);
                                } else {
                                    $child = $this->_parseRule($child, $data);
                                }
                                $childrenSchema[$i] = $child;
                            }
                            $children = $childrenSchema;
                        } else {
                            $children = array(
                                $this->_parseRule($child, $data),
                            );
                        }
                    } else if (preg_match("/^(\d+)-(\d+)$/", $k, $match)) {
                        $min = $match[1];
                        $max = $match[2];
                        for ($i = $min; $i <= $max; $i++) {
                            if (isset($child['fields'])) {
                                $cdata = isset($data[$key][$i]) ? $data[$key][$i] : array();
                                $child['children'] = $this->_initSchema($child, $cdata);
                            } else {
                                $child = $this->_parseRule($child, $data);
                            }
                            $childrenSchema[$i] = $child;
                        }
                        $children = $childrenSchema;
                    } else {
                        if (isset($child['fields'])) {
                            $cdata = isset($data[$key][$k]) ? $data[$key][$k] : array();
                            $child['children'] = $this->_initSchema($child, $cdata);
                            $children[$k] = $child;
                        } else {
                            $children[$k]['children'] = $this->_parseRule($child, $data);
                        }
                    }
                }
                unset($rule['fields']);
                $rule['children'] = $children;
            }
            $schema[$key] = $this->_parseRule($rule, $data);
        }
        return $schema;
    }

    /**
     * _parseRule
     *
     * @param array $rule rule setting
     * @param array $data data
     *
     * @return array
     */
    private function _parseRule($rule, $data) {
        if (is_array($rule['rule'])) {
            foreach ($rule['rule'] as $key => $value) {
                if ($value == 'default') {
                    $rule['rule'] = $key;
                } else if (is_array($value)) {
                    $flag = true;
                    foreach ($value as $fKey => $fVal) {
                        $cVal = $this->_hasKeyPathValue($data, $fKey);
                        if (is_array($fVal)) {
                            if (!in_array($cVal, $fVal)) {
                                $flag = false;
                                break;
                            }
                        } else if ($cVal != $fVal) {
                            $flag = false;
                            break;
                        }
                    }
                    if ($flag === true) {
                        $rule['rule'] = $key;
                    }
                }
            }
        }
        return $rule;
    }

    /**
     * _initTranslater
     *
     * @param \loeye\base\AppConfig $appConfig AppConfig instance
     *
     * @return void
     */
    private function _initTranslater(AppConfig $appConfig) {
        $this->translater = new I18n\Translator($appConfig->getLocale());
        $loader = new I18n\Loader\XliffFileLoader();
        $resourseDir = PROJECT_DIR . '/../vendor/symfony/validator/Resources/translations/';
        foreach (new \FilesystemIterator($resourseDir, \FilesystemIterator::KEY_AS_FILENAME) as $key => $item) {
            if (!$item->isFile()) {
                continue;
            }
            $lpos = strpos($key, ".");
            $rpos = strrpos($key, ".");
            $locale = substr($key, $lpos + 1, $rpos - $lpos - 1);
            $this->translater->addResource('xlf', $item->getRealPath(), $locale);
        }
        $this->translater->addLoader('xlf', $loader);
    }

    /**
     * _hasKeyPathValue
     *
     * @param array  $data    data
     * @param string $keyPath key path
     *
     * @return boolean
     */
    private function _hasKeyPathValue($data, $keyPath) {
        $current = $data;
        $keyArray = explode(">", $keyPath);
        while ($key = array_shift($keyArray)) {
            if (!isset($current[$key])) {
                return false;
            }
            $current = $current[$key];
        }
        return $current;
    }

    /**
     * _filter
     *
     * @param array $data
     * @param ConstraintViolationList $violationList
     * @param type $pkey
     * @return array
     */
    private function _filter(array $data, ConstraintViolationList $violationList, $pkey = null) {
        $errmsg = $this->_buildErrmsg($violationList);
        foreach ($errmsg as $key => $value) {
            unset($data[$key]);
        }
        $this->_report['error_message'] = array_merge($this->_report['error_message'], ($pkey ? [$pkey => $errmsg] : $errmsg));
        return $data;
    }

    /**
     * _buildErrmsg
     * @param ConstraintViolationList $violationList
     * @return type
     */
    private function _buildErrmsg(ConstraintViolationList $violationList) {
        $error = [];
        for ($i = 0; $i < $violationList->count(); $i++) {
            $violation = $violationList->get($i);
            $propertyPath = $violation->getPropertyPath();
            $msg = $this->translater->trans($violation->getMessageTemplate(), $violation->getParameters());
            $offset = 0;
            $property = [];
            while (preg_match('/\[([^\]]+)\]/', $propertyPath, $matches, PREG_OFFSET_CAPTURE, $offset)) {
                $offset = $matches[0][1] + strlen($matches[0][0]);
                array_push($property, $matches[1][0]);
            }
            $err = $msg;
            $c = count($property);
            for ($j = 0; $j < $c; $j++) {
                $key = array_pop($property);
                if ($j == $c - 1) {
                    $error[$key] = $err;
                } else {
                    $err = [$key => $err];
                }
            }
        }
        return $error;
    }

}
