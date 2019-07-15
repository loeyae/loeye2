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

/**
 * Description of Validator
 *
 * @author   Zhang Yi <loeyae@gmail.com>
 */
class Validator
{

    use \loeye\std\ConfigTrait;

    private $_report;

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
    )
    {
        $this->config  = $this->bundleConfig($appConfig->getPropertyName(), $bundle);
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
    public function validate($data, $rule)
    {
        $f = function($item) use (&$f) {
            if (is_array($item) || $item instanceof \Traversable || $item instanceof \ArrayAccess) {
                return array_map($f, $item);
            }
            return filter_var($item, FILTER_SANITIZE_FULL_SPECIAL_CHARS, FILTER_FLAG_NO_ENCODE_QUOTES);
        };
        $fdata        = $f($data);
        $rulesets     = $this->config->get('rulesets');
        $validateSets = $this->config->getConfig(null, self::KEYWORD . '=' . $rule);
        if (empty($validateSets[self::KEYWORD]) || empty($validateSets[self::KEYWORD]['fields'])) {
            throw new BusinessException(
                BusinessException::INVALID_CONFIG_SET_MSG, BusinessException::INVALID_CONFIG_SET_CODE);
        }
        $schema         = $this->_initSchema($validateSets[self::KEYWORD]['fields'], $data);
        $validator      = Validation::createValidator();
        $constraintList = $this->_buildConstraintList($rulesets, $schema, $data);
        $violationList  = new ConstraintViolationList();
        if ($constraintList['main']) {
            $constraint    = new Assert\Collection([
                'fields'           => $constraintList['main'],
                'allowExtraFields' => true,
            ]);
            $violationList = $validator->validate($data, $constraint);
        }
        $validData = $this->_filter($fdata, $violationList);
        if ($constraintList['sub']) {
            foreach ($constraintList['sub'] as $key => $item) {
                if (array_key_exists($key, $validData)) {
                    $constraint      = new Assert\Collection([
                        'fields'           => $item['main'],
                        'allowExtraFields' => true,
                    ]);
                    $violationList   = $validator->validate($data[$key], $constraint);
                    $validSubData    = $this->_filter($validData[$key], $violationList, $key);
                    $validData[$key] = $validSubData;
                }
            }
        }
        if (!empty($this->_report['error_message'])) {
            $this->_report['has_error'] = true;
        }
        $this->_report['valid_data'] = $validData;
        return $this->_report;
    }

    private function _buildConstraintList($rulesets, $schema, $data)
    {
        $validationList = [
            "main" => [],
            "sub"  => [],
        ];
        $requiredFields = [];
        foreach ($schema as $key => $rule) {
            $ruleset = $rulesets[$rule['rule']] ?? null;
            if (!$ruleset) {
                throw new Exception('Invalid rulesets: ' . $rule['rule']);
            }
            $validator = $this->_buildConstraint($ruleset, $rule);
            if ($validator) {
                $validationList['main'][$key] = $validator;
            }
            $requiredFields = array_replace($requiredFields, $this->_getRequiredFields($key, $rule, $data));
            if (isset($rule['children'])) {
                $validationList['sub'][$key] = $this->_buildConstraintList($rulesets, $rule['children'], $data);
            }
        }
        foreach ($validationList['main'] as $key => $constraintList) {
            if (array_key_exists($key, $requiredFields)) {
                if ($requiredFields[$key] == 'required_value') {
                    array_unshift($constraintList, new Assert\NotBlank());
                    array_unshift($constraintList, new Assert\NotNull());
                }
                $validator = new Assert\Required($constraintList);
            } else {
                $validator = new Assert\Optional($constraintList);
            }
            $validationList['main'][$key] = $validator;
        }
        return $validationList;
    }

    private function _getRequiredFields($key, $rule, $data)
    {
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

    private function _buildConstraint($ruleset, $setting)
    {
        $typeConstraint     = $this->_buildTypeConstraint($ruleset);
        $lengthConstraint   = $this->_buildLengthConstraint($ruleset);
        $countConstraint    = $this->_buildCountConstraint($ruleset);
        $rangeConstraint    = $this->_buildRangeConstraint($ruleset);
        $regexConstraint    = $this->_buildRegexConstraint($ruleset);
        $choiceConstraint   = $this->_buildChoiceConstraint($ruleset);
        $operatorConstraint = $this->_buildOperatorConstraint($ruleset);
        $validatorList      = array_merge($typeConstraint, $lengthConstraint, $countConstraint, $rangeConstraint, $regexConstraint, $choiceConstraint, $operatorConstraint);
        return $validatorList;
    }

    private function _buildLengthConstraint($ruleset)
    {
        $lengthKeys = ['length', 'min_length', 'max_length'];
        if (array_intersect_key($ruleset, array_fill_keys($lengthKeys, null))) {
            $options                 = [
                'min' => $ruleset['min_length'] ?? ($ruleset['length'][0] ?? $ruleset['length']),
                'max' => $ruleset['max_length'] ?? ($ruleset['length'][1] ?? $ruleset['length']),
            ];
            $validator               = new Assert\Length($options);
            !$ruleset['length_errmsg'] ?: $validator->exactMessage = $ruleset['length_errmsg'];
            !$ruleset['min_length_errmsg'] ?: $validator->minMessage   = $ruleset['min_length_errmsg'];
            !$ruleset['max_length_errmsg'] ?: $validator->maxMessage   = $ruleset['max_length_errmsg'];
            return [$validator];
        }
        return [];
    }

    private function _buildCountConstraint($ruleset)
    {
        $countKeys = ['count', 'min_count', 'max_count'];
        if (array_intersect_key($ruleset, array_fill_keys($countKeys, null))) {
            $options                 = [
                'min' => $ruleset['min_count'] ?? ($ruleset['count'][0] ?? $ruleset['count']),
                'max' => $ruleset['max_count'] ?? ($ruleset['count'][1] ?? $ruleset['count']),
            ];
            $validator               = new Assert\Length($options);
            !$ruleset['count_errmsg'] ?: $validator->exactMessage = $ruleset['count_errmsg'];
            !$ruleset['min_count_errmsg'] ?: $validator->minMessage   = $ruleset['min_count_errmsg'];
            !$ruleset['max_count_errmsg'] ?: $validator->maxMessage   = $ruleset['max_count_errmsg'];
            return [$validator];
        }
        return [];
    }

    private function _buildRangeConstraint($ruleset)
    {
        $rangeKeys = ['range', 'min', 'max'];
        if (array_intersect_key($ruleset, array_fill_keys($rangeKeys, null))) {
            $options               = [
                'min' => $ruleset['min'] ?? ($ruleset['range'][0] ?? $ruleset['range']),
                'max' => $ruleset['max'] ?? ($ruleset['range'][1] ?? $ruleset['range']),
            ];
            $validator             = new Assert\Length($options);
            !$ruleset['min_errmsg'] ?: $validator->minMessage = $ruleset['min_errmsg'];
            !$ruleset['max_errmsg'] ?: $validator->maxMessage = $ruleset['max_errmsg'];
            return [$validator];
        }
        return [];
    }

    private function _buildRegexConstraint($ruleset)
    {
        if (isset($ruleset['regex'])) {
            $options   = [
                'pattern'     => $ruleset['regex'],
                'htmlPattern' => $ruleset['html_pattern'] ?? null,
                'match'       => $ruleset['match'] ?? true,
            ];
            $validator = new Assert\Regex($options);
            return [$validator];
        }
        return [];
    }

    private function _buildChoiceConstraint($ruleset)
    {
        if (isset($ruleset['choice'])) {
            $options                    = [
                'choices'  => $ruleset['choice'],
                'callback' => $ruleset['choice_callback'] ?? null,
                'multiple' => $ruleset['choice_multiple'] ?? false,
                'min'      => $ruleset['choice_min'] ?? null,
                'max'      => $ruleset['choice_max'] ?? null,
            ];
            $validator                  = new Assert\Choice($options);
            !$ruleset['choice_errmsg'] ?: $validator->message         = $ruleset['choice_errmsg'];
            !$ruleset['choice_multiple_errmsg'] ?: $validator->multipleMessage = $ruleset['choice_multiple_errmsg'];
            !$ruleset['choice_min_errmsg'] ?: $validator->minMessage      = $ruleset['choice_min_errmsg'];
            !$ruleset['choice_max_errmsg'] ?: $validator->maxMessage      = $ruleset['choice_max_errmsg'];
            return [$validator];
        }
        return [];
    }

    private function _buildOperatorConstraint($ruleset)
    {
        $validatorList   = [];
        $operatorMapping = [
            '='  => 'EqualTo',
            '==' => 'IdenticalTo',
            '>'  => 'LessThan',
            '>=' => 'LessThanOrEqual',
            '<'  => 'GreaterThan',
            '<=' => 'GreaterThanOrEqual'
        ];
        $intersect       = array_intersect_key($ruleset, $operatorMapping);
        if ($intersect) {
            foreach ($intersect as $key => $value) {
                $item               = $operatorMapping[$key];
                $rc                 = \ReflectionClass('\\Symfony\\Component\\Validator\\Constraints\\' . ucfirst($item));
                $validator          = $rc->newInstance(['value' => $value]);
                !$ruleset[$key . '_errmsg'] ?: $validator->message = $ruleset[$key . '_errmsg'];
                $validatorList[]    = $validator;
            }
        }
        return $validatorList;
    }

    private function _buildTypeConstraint($ruleset)
    {
        $type      = $ruleset['type'] ?? null;
        $message   = $ruleset['type_errmsg'] ?? null;
        $validator = null;
        if ($type && function_exists('\\is_' . $type)) {
            $validator = new Assert\Type(['type' => $type]);
        } else if ($type && class_exists('\\Symfony\\Component\\Validator\\Constraints\\' . ucfirst($type))) {
            $rc        = new \ReflectionClass('\\Symfony\\Component\\Validator\\Constraints\\' . ucfirst($type));
            $validator = $rc->newInstanceArgs();
            if ($type == 'image') {
                $validator->mimeTypes        = $ruleset['mim-types'] ?? 'image/*';
                !$ruleset['mim-types_errmsg'] ?: $validator->mimeTypesMessage = $ruleset['mim-types_errmsg'];
                $validator->minWidth         = $ruleset['min_width'] ?? null;
                !$ruleset['min_width_errmsg'] ?: $validator->minWidthMessage  = $ruleset['min_width_errmsg'];
                $validator->maxWidth         = $ruleset['max_width'] ?? null;
                !$ruleset['max_width_errmsg'] ?: $validator->maxWidthMessage  = $ruleset['mmax_width_errmsg'];
                $validator->maxHeight        = $ruleset['max_height'] ?? null;
                !$ruleset['max_height_errmsg'] ?: $validator->maxHeightMessage = $ruleset['max_height_errmsg'];
                $validator->minHeight        = $ruleset['min_height'] ?? null;
                !$ruleset['min_height_errmsg'] ?: $validator->minHeightMessage = $ruleset['min_height_errmsg'];
                $validator->maxRatio         = $ruleset['max_ratio'] ?? null;
                !$ruleset['max_ratio_errmsg'] ?: $validator->maxRatioMessage  = $ruleset['max_ratio_errmsg'];
                $validator->minRatio         = $ruleset['min_ratio'] ?? null;
                !$ruleset['min_ratio_errmsg'] ?: $validator->minRatioMessage  = $ruleset['min_ratio_errmsg'];
                $validator->minPixels        = $ruleset['min_pixels'] ?? null;
                !$ruleset['min_pixels_errmsg'] ?: $validator->minPixelsMessage = $ruleset['min_pixels_errmsg'];
                $validator->maxPixels        = $ruleset['max_pixels'] ?? null;
                !$ruleset['max_pixels_errmsg'] ?: $validator->maxPixelsMessage = $ruleset['max_pixels_errmsg'];
                $validator->maxSize          = $ruleset['max_size'] ?? null;
                !$ruleset['max_size_errmsg'] ?: $validator->maxSizeMessage   = $ruleset['max_size_errmsg'];
            } else if ($type == 'file') {
                $validator->mimeTypes        = $ruleset['mim-types'] ?? [];
                !$ruleset['mim-types_errmsg'] ?: $validator->mimeTypesMessage = $ruleset['mim-types_errmsg'];
                $validator->maxSize          = $ruleset['max_size'] ?? null;
                !$ruleset['max_size_errmsg'] ?: $validator->maxSizeMessage   = $ruleset['max_size_errmsg'];
            } else if ($type == 'ip') {
                $validator->version = $ruleset['version'] ?? Assert\Ip::V4;
            }
        } else if ($type) {
            throw new Exception('No Support type: ' . $type);
        }
        (!$message && $validator) ?: $validator->message = $message;
        return $validator ? [$validator] : [];
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
    private function _initSchema($schema, $data)
    {
        foreach ($schema as $key => $rule) {
            if (isset($rule['fields'])) {
                $children = $rule['fields'];
                foreach ($children as $k => $child) {
                    if (empty($child) || !is_array($child)) {
                        throw new BusinessException(
                            BusinessException::INVALID_CONFIG_SET_MSG, BusinessException::INVALID_CONFIG_SET_CODE);
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
                                $cdata             = isset($data[$key][$i]) ? $data[$key][$i] : array();
                                $child['children'] = $this->_initSchema($child, $cdata);
                            } else {
                                $child = $this->_parseRule($child, $data);
                            }
                            $childrenSchema[$i] = $child;
                        }
                        $children = $childrenSchema;
                    } else {
                        if (isset($child['fields'])) {
                            $cdata             = isset($data[$key][$k]) ? $data[$key][$k] : array();
                            $child['children'] = $this->_initSchema($child, $cdata);
                            $children[$k]      = $child;
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
    private function _parseRule($rule, $data)
    {
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
    private function _initTranslater(AppConfig $appConfig)
    {
        $this->translater = new I18n\Translator($appConfig->getLocale());
        $loader           = new I18n\Loader\XliffFileLoader();
        $resourseDir      = PROJECT_DIR . '/../vendor/symfony/validator/Resources/translations/';
        foreach (new \FilesystemIterator($resourseDir, \FilesystemIterator::KEY_AS_FILENAME) as $key => $item) {
            if (!$item->isFile()) {
                continue;
            }
            $lpos   = strpos($key, ".");
            $rpos   = strrpos($key, ".");
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
    private function _hasKeyPathValue($data, $keyPath)
    {
        $current  = $data;
        $keyArray = explode(">", $keyPath);
        while ($key      = array_shift($keyArray)) {
            if (!isset($current[$key])) {
                return false;
            }
            $current = $current[$key];
        }
        return $current;
    }

    private function _filter(array $data, ConstraintViolationList $violationList, $pkey = null)
    {
        $errmsg = $this->_buildErrmsg($violationList);
        foreach ($errmsg as $key => $value) {
            unset($data[$key]);
        }
        $this->_report['error_message'] = array_merge($this->_report['error_message'], ($pkey ? [$pkey => $errmsg] : $errmsg));
        return $data;
    }

    private function _buildErrmsg(ConstraintViolationList $violationList)
    {
        $error = [];
        for ($i = 0; $i < $violationList->count(); $i++) {
            $violation    = $violationList->get($i);
            $propertyPath = $violation->getPropertyPath();
            $msg          = $this->translater->trans($violation->getMessageTemplate(), $violation->getParameters());
            $offset       = 0;
            $property     = [];
            while (preg_match('/\[([^\]]+)\]/', $propertyPath, $matches, PREG_OFFSET_CAPTURE, $offset)) {
                $offset = $matches[0][1] + strlen($matches[0][0]);
                array_push($property, $matches[1][0]);
            }
            $err = $msg;
            $c   = count($property);
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
