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

namespace loeye\validate;

use FilesystemIterator;
use loeye\base\AppConfig;
use loeye\base\Configuration;
use loeye\base\Exception;
use loeye\base\Factory;
use loeye\config\validate\DeltaConfigDefinition;
use loeye\config\validate\RulesetConfigDefinition;
use loeye\error\BusinessException;
use loeye\std\ConfigTrait;
use ReflectionClass;
use ReflectionException;
use Symfony\Component\Translation as I18n;
use Symfony\Component\Translation\Translator;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Constraints\All;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Context\ExecutionContext;
use Symfony\Component\Validator\Validation;

/**
 * Description of Validator
 *
 * @author   Zhang Yi <loeyae@gmail.com>
 */
class Validator
{

    use ConfigTrait;

    private $_report;

    /**
     *
     * @var AppConfig
     */
    protected $appConfig;

    /**
     *
     * @var Configuration
     */
    protected $config;

    public const LLT_PREFIX = 'LV_FIELD_';
    public const KEYWORD = 'validate';
    public const BUNDLE = 'validate';

    protected $prefix;

    /**
     * __construct
     *
     * @param AppConfig $appConfig AppConfig instance
     * @param string|null $bundle bundle
     */
    public function __construct(AppConfig $appConfig, $bundle = null)
    {
        $this->appConfig = $appConfig;
        $definition = [new RulesetConfigDefinition(), new DeltaConfigDefinition()];
        $this->config = $this->bundleConfig($appConfig->getPropertyName(), $bundle, $definition);
        $this->_report = array('has_error' => false, 'error_message' => []);
    }

    /**
     * validate
     *
     * @param array $data data
     * @param string $rule rule
     *
     * @return array()
     * @throws BusinessException
     * @throws Exception
     * @throws ReflectionException
     */
    public function validate($data, $rule): array
    {
        $ruleSets = $this->config->get('rulesets');
        $validateSets = $this->config->getConfig(null, self::KEYWORD . '=' . $rule);
        if (empty($validateSets[self::KEYWORD]) || empty($validateSets[self::KEYWORD]['fields'])) {
            throw new BusinessException(
                BusinessException::INVALID_CONFIG_SET_MSG, BusinessException::INVALID_CONFIG_SET_CODE);
        }
        $schema = $this->_initSchema($validateSets[self::KEYWORD]['fields'], $data);
        $fData = $this->_filterData($ruleSets, $schema, $data);
        $validator = Validation::createValidator();
        $constraintList = $this->_buildConstraintList($ruleSets, $schema, $data);
        $violationList = new ConstraintViolationList();
        if ($constraintList) {
            $constraint = new Assert\Collection([
                'fields' => $constraintList,
                'allowExtraFields' => true,
            ]);
            $violationList = $validator->validate($data, $constraint);
        }
        $validData = $this->_filter($fData, $violationList);
        if (!empty($this->_report['error_message'])) {
            $this->_report['has_error'] = true;
        }
        $this->_report['valid_data'] = $validData;
        return $this->_report;
    }

    /**
     * _buildConstraintList
     *
     * @param $ruleSets
     * @param $schema
     * @param $data
     * @return array
     * @throws BusinessException
     * @throws ReflectionException
     */
    private function _buildConstraintList($ruleSets, $schema, $data): array
    {
        $validationList = [];
        $requiredFields = [];
        foreach ($schema as $key => $rule) {
            $validator = $this->_buildConstraint($ruleSets, $rule);
            if ($validator) {
                $validationList[$key] = $validator;
            }
            $fields = $requiredFields;
            $requiredFields = array_replace($fields, $this->_getRequiredFields($key, $rule, $data));
        }
        foreach ($validationList as $key => $constraintList) {
            if (array_key_exists($key, $requiredFields)) {
                if ($requiredFields[$key] === 'required_value') {
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

    /**
     * _getRequiredFields
     *
     * @param string $key key
     * @param array $rule rule
     * @param array $data data
     *
     * @return array
     */
    private function _getRequiredFields($key, $rule, $data): array
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
                if ($data[$key] === $v) {
                    $fields = $requiredFields;
                    $requiredFields = array_replace($fields, array_fill_keys((array)$f, 'required_value'));
                }
            }
        }
        if (isset($rule['required_value_if_include'])) {
            foreach ($rule['required_value_if_include'] as $v => $f) {
                if (in_array($v, $data[$key], true)) {
                    $fields = $requiredFields;
                    $requiredFields = array_replace($fields, array_fill_keys((array)$f, 'required_value'));
                }
            }
        }
        if (isset($rule['required_value_if_key_exists'], $data[$key])) {
            $requiredFields = [$key => 'required_value'];
        }
        if (isset($rule['required_value_if_blank'], $data[$key]) && $data[$key] === '') {
            $requiredFields = array_replace($requiredFields, array_fill_keys((array)$rule['required_value_if_blank'], 'required_value'));
        }
        return $requiredFields;
    }

    /**
     * _buildConstraint
     *
     * @param array $ruleSets rules
     * @param array $setting setting
     *
     * @return array
     * @throws BusinessException
     * @throws ReflectionException
     */
    private function _buildConstraint($ruleSets, $setting): array
    {
        $ruleset = $ruleSets[$setting['rule']] ?? null;
        if (!$ruleset) {
            throw new BusinessException(BusinessException::INVALID_CONFIG_SET_MSG, BusinessException::INVALID_CONFIG_SET_CODE, ['setting' => 'validate ruleset: ' . $setting['rule']]);
        }
        $ruleset = array_filter($ruleset, static function ($item) {
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
            $allConstraint = new All($this->_buildConstraint($ruleSets, $setting['item']));
            $validatorList[] = $allConstraint;
        }
        return $validatorList;
    }

    /**
     * _buildLengthConstraint
     *
     * @param array $ruleset ruleset
     *
     * @return array
     */
    private function _buildLengthConstraint($ruleset): array
    {
        $lengthKeys = ['length', 'min_length', 'max_length'];
        if (array_intersect_key($ruleset, array_fill_keys($lengthKeys, null))) {
            $options = [
                'min' => ($ruleset['min_length'] ?? ($ruleset['length'][0] ?? ($ruleset['length'] ?? null))),
                'max' => ($ruleset['max_length'] ?? ($ruleset['length'][1] ?? ($ruleset['length'] ?? null))),
            ];
            $validator = new Assert\Length($options);
            !isset($ruleset['length_errmsg']) ?: $validator->exactMessage = $ruleset['length_errmsg'];
            !isset($ruleset['min_length_errmsg']) ?: $validator->minMessage = $ruleset['min_length_errmsg'];
            !isset($ruleset['max_length_errmsg']) ?: $validator->maxMessage = $ruleset['max_length_errmsg'];
            return [$validator];
        }
        return [];
    }

    /**
     * _buildCountConstraint
     *
     * @param array $ruleset ruleset
     * @return array
     */
    private function _buildCountConstraint($ruleset): array
    {
        $countKeys = ['count', 'min_count', 'max_count'];
        if (array_intersect_key($ruleset, array_fill_keys($countKeys, null))) {
            $options = [
                'min' => ($ruleset['min_count'] ?? ($ruleset['count'][0] ?? ($ruleset['count'] ?? null))),
                'max' => ($ruleset['max_count'] ?? ($ruleset['count'][1] ?? ($ruleset['count'] ?? null))),
            ];
            $validator = new Assert\Count($options);
            !isset($ruleset['count_errmsg']) ?: $validator->exactMessage = $ruleset['count_errmsg'];
            !isset($ruleset['min_count_errmsg']) ?: $validator->minMessage = $ruleset['min_count_errmsg'];
            !isset($ruleset['max_count_errmsg']) ?: $validator->maxMessage = $ruleset['max_count_errmsg'];
            return [$validator];
        }
        return [];
    }

    /**
     * _buildRangeConstraint
     *
     * @param array $ruleset ruleset
     * @return array
     */
    private function _buildRangeConstraint($ruleset): array
    {
        $rangeKeys = ['range', 'min', 'max'];
        if (array_intersect_key($ruleset, array_fill_keys($rangeKeys, null))) {
            $options = [
                'min' => ($ruleset['min'] ?? ($ruleset['range'][0] ?? ($ruleset['range'] ?? null))),
                'max' => ($ruleset['max'] ?? ($ruleset['range'][1] ?? ($ruleset['range'] ?? null))),
            ];
            $validator = new Assert\Range($options);
            !isset($ruleset['min_errmsg']) ?: $validator->minMessage = $ruleset['min_errmsg'];
            !isset($ruleset['max_errmsg']) ?: $validator->maxMessage = $ruleset['max_errmsg'];
            return [$validator];
        }
        return [];
    }

    /**
     * _buildRegexConstraint
     *
     * @param array $ruleset ruleset
     * @return array
     */
    private function _buildRegexConstraint($ruleset): array
    {
        if (isset($ruleset['regex']) && $ruleset['regex']) {
            $options = [
                'pattern' => $ruleset['regex']['pattern'] ?? null,
                'htmlPattern' => $ruleset['regex']['html_pattern'] ?? null,
                'match' => $ruleset['regex']['match'] ?? true,
            ];
            $validator = new Assert\Regex($options);
            return [$validator];
        }
        return [];
    }

    /**
     * _buildChoiceConstraint
     *
     * @param array $ruleset ruleset
     * @return array
     */
    private function _buildChoiceConstraint($ruleset): array
    {
        if (isset($ruleset['choice']) && $ruleset['choice']) {
            $options = [
                'choices' => $ruleset['choice'],
                'callback' => $ruleset['choice_callback'] ?? null,
                'multiple' => $ruleset['choice_multiple'] ?? false,
                'min' => $ruleset['choice_min'] ?? null,
                'max' => $ruleset['choice_max'] ?? null,
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

    /**
     * _buildOperatorConstraint
     *
     * @param array $ruleset ruleset
     * @return array
     * @throws ReflectionException
     */
    private function _buildOperatorConstraint($ruleset): array
    {
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
                $rc = new ReflectionClass('\\Symfony\\Component\\Validator\\Constraints\\' . ucfirst($item));
                $validator = $rc->newInstance(['value' => $value]);
                !$ruleset[$key . '_errmsg'] ?: $validator->message = $ruleset[$key . '_errmsg'];
                $validatorList[] = $validator;
            }
        }
        return $validatorList;
    }

    /**
     * _buildTypeConstraint
     *
     * @param array $ruleset ruleset
     * @return array
     * @throws BusinessException
     * @throws ReflectionException
     */
    private function _buildTypeConstraint($ruleset): array
    {
        $type = $ruleset['type'] ?? null;
        $message = $ruleset['type_errmsg'] ?? null;
        $validator = null;
        if ($type && function_exists('\\is_' . $type)) {
            $validator = new Assert\Type(['type' => $type]);
        } else if ($type && class_exists('\\Symfony\\Component\\Validator\\Constraints\\' . ucfirst($type))) {
            $rc = new ReflectionClass('\\Symfony\\Component\\Validator\\Constraints\\' . ucfirst($type));
            $validator = $rc->newInstanceArgs();
            if ($type === 'image') {
                $validator->mimeTypes = ($ruleset['mim-types'] ?? 'image/*');
                !isset($ruleset['mim-types_errmsg']) ?: $validator->mimeTypesMessage = $ruleset['mim-types_errmsg'];
                $validator->minWidth = $ruleset['min_width'] ?? null;
                !isset($ruleset['min_width_errmsg']) ?: $validator->minWidthMessage = $ruleset['min_width_errmsg'];
                $validator->maxWidth = ($ruleset['max_width'] ?? null);
                !isset($ruleset['max_width_errmsg']) ?: $validator->maxWidthMessage = $ruleset['mmax_width_errmsg'];
                $validator->maxHeight = ($ruleset['max_height'] ?? null);
                !isset($ruleset['max_height_errmsg']) ?: $validator->maxHeightMessage = $ruleset['max_height_errmsg'];
                $validator->minHeight = ($ruleset['min_height'] ?? null);
                !isset($ruleset['min_height_errmsg']) ?: $validator->minHeightMessage = $ruleset['min_height_errmsg'];
                $validator->maxRatio = ($ruleset['max_ratio'] ?? null);
                !isset($ruleset['max_ratio_errmsg']) ?: $validator->maxRatioMessage = $ruleset['max_ratio_errmsg'];
                $validator->minRatio = ($ruleset['min_ratio'] ?? null);
                !isset($ruleset['min_ratio_errmsg']) ?: $validator->minRatioMessage = $ruleset['min_ratio_errmsg'];
                $validator->minPixels = ($ruleset['min_pixels'] ?? null);
                !isset($ruleset['min_pixels_errmsg']) ?: $validator->minPixelsMessage = $ruleset['min_pixels_errmsg'];
                $validator->maxPixels = ($ruleset['max_pixels'] ?? null);
                !isset($ruleset['max_pixels_errmsg']) ?: $validator->maxPixelsMessage = $ruleset['max_pixels_errmsg'];
                $validator->maxSize = ($ruleset['max_size'] ?? null);
                !isset($ruleset['max_size_errmsg']) ?: $validator->maxSizeMessage = $ruleset['max_size_errmsg'];
            } else if ($type === 'file') {
                $validator->mimeTypes = ($ruleset['mim-types'] ?? []);
                !isset($ruleset['mim-types_errmsg']) ?: $validator->mimeTypesMessage = $ruleset['mim-types_errmsg'];
                $validator->maxSize = ($ruleset['max_size'] ?? null);
                !isset($ruleset['max_size_errmsg']) ?: $validator->maxSizeMessage = $ruleset['max_size_errmsg'];
            } else if ($type === 'ip') {
                $validator->version = ($ruleset['version'] ?? Assert\Ip::V4);
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
     * @param array $ruleset
     * @return array
     */
    private function _buildCallbackConstraint($ruleset): array
    {
        $fun = $ruleset['callback'] ?? null;
        $msg = $ruleset['callback_message'] ?? null;
        $validatorList = [];
        if ($fun && is_iterable($fun)) {
            foreach ($fun as $item) {
                $callback = $item['name'];
                $message = $item['message'] ?? $msg;
                $validatorList[] = $this->_buildCallbackValidator($callback, $message);
            }
        }
        return $validatorList;
    }

    /**
     * _buildCallbackValidator
     *
     * @param callable $callback
     * @param string $message
     * @return mixed
     */
    private function _buildCallbackValidator(callable $callback, $message)
    {
        $appConfig = $this->appConfig;
        $f = static function ($object, ExecutionContext $context, $payload) use ($appConfig, $callback, $message) {
            if (!$callback($object, $appConfig)) {
                $context->buildViolation($message)
                    ->setParameter('{{ value }}', $object)
                    ->setCode(uniqid('', true))
                    ->addViolation();
            }
        };
        $options = [
            'callback' => $f
        ];
        return new Callback($options);
    }

    /**
     * _filterData
     *
     * @param array $ruleSets ruleset list
     * @param array $schema schema
     * @param array $data data
     * @return array
     * @throws BusinessException
     */
    private function _filterData($ruleSets, $schema, $data): array
    {
        $filteredData = [];
        foreach ($data as $key => $value) {
            if (isset($schema[$key])) {
                $ruleset = $ruleSets[$schema[$key]['rule']] ?? null;
                if (!$ruleset) {
                    throw new BusinessException(BusinessException::INVALID_CONFIG_SET_MSG, BusinessException::INVALID_CONFIG_SET_CODE, ['setting' => 'validate ruleset: ' . $schema[$key]['rule']]);
                }
                if (!empty($ruleset['filter'])) {
                    $filteredData[$key] = $this->_filterVar($value, $ruleset);
                    continue;
                }
            }
            $filteredData[$key] = filter_var($value, FILTER_SANITIZE_FULL_SPECIAL_CHARS, FILTER_FLAG_NO_ENCODE_QUOTES);
        }
        return $filteredData;
    }

    /**
     * _filterVar
     *
     * @param array $data data
     * @param array $ruleset ruleset
     * @return array
     */
    private function _filterVar($data, $ruleset): array
    {
        if (is_iterable($data)) {
            $filtered = [];
            foreach ($data as $key => $value) {
                $filtered[$key] = $this->_filterVar($value, $ruleset);
            }
            return $filtered;
        }
        $filter = isset($ruleset['filter']['filter_type']) ? constant($ruleset['filter']['filter_type']) : FILTER_SANITIZE_FULL_SPECIAL_CHARS;
        $ops = [];
        if (!empty($ruleset['filter']['options'])) {
            if (is_iterable($ruleset['filter']['options'])) {
                $ops = $ruleset['filter']['options'];
            } else {
                $ops['flag'] = $ruleset['filter']['options'];
            }
        }
        !isset($ruleset['filter']['filter_flag']) ?: $ops['flag'] = constant($ruleset['filter']['filter_flag']);
        !isset($ruleset['filter']['filter_options']) ?: $ops['options'] = $ruleset['filter']['filter_options'];
        $validated = filter_var($data, $filter, $ops);
        if (($validated !== false) && !empty($ruleset['fun'])) {
            foreach ($ruleset['fun'] as $funSet) {
                $fun = $funSet['name'];
                if (is_callable($fun)) {
                    $params = (array )($funSet['params'] ?? []);
                    array_unshift($params, $validated);
                    $validated = call_user_func_array($fun, $params);
                }
            }
        }
        return $validated;
    }

    /**
     * _initSchema
     *
     * @param array $schema rule name
     * @param array $data data
     *
     * @return array
     * @throws Exception
     */
    private function _initSchema($schema, $data): array
    {
        foreach ($schema as $key => $rule) {
            if (isset($rule['fields'])) {
                $children = $rule['fields'];
                foreach ($children as $k => $child) {
                    if (empty($child) || !is_array($child)) {
                        throw new BusinessException(
                            BusinessException::INVALID_CONFIG_SET_MSG, BusinessException::INVALID_CONFIG_SET_CODE, ['setting' => 'validate ruleset child']);
                    }
                    $childrenSchema = array();
                    if ($k === 'i') {
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
                        [$min, $max] = $match;
                        for ($i = $min; $i <= $max; $i++) {
                            if (isset($child['fields'])) {
                                $cdata = $data[$key][$i] ?? array();
                                $child['children'] = $this->_initSchema($child, $cdata);
                            } else {
                                $child = $this->_parseRule($child, $data);
                            }
                            $childrenSchema[$i] = $child;
                        }
                        $children = $childrenSchema;
                    } else if (isset($child['fields'])) {
                        $cdata = $data[$key][$k] ?? array();
                        $child['children'] = $this->_initSchema($child, $cdata);
                        $children[$k] = $child;
                    } else {
                        $children[$k]['children'] = $this->_parseRule($child, $data);
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
    private function _parseRule($rule, $data): array
    {
        if (is_array($rule['rule'])) {
            foreach ($rule['rule'] as $key => $value) {
                if ($value === 'default') {
                    $rule['rule'] = $key;
                } else if (is_array($value)) {
                    $flag = true;
                    foreach ($value as $fKey => $fVal) {
                        $cVal = $this->_hasKeyPathValue($data, $fKey);
                        if (is_array($fVal)) {
                            if (!in_array($cVal, $fVal, true)) {
                                $flag = false;
                                break;
                            }
                        } else if ($cVal !== $fVal) {
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
     * initTranslator
     *
     * @param AppConfig $appConfig AppConfig instance
     *
     * @return Translator
     */
    public static function initTranslator(AppConfig $appConfig): Translator
    {
        $translator = Factory::translator($appConfig)->getTranslator();
        $loader = new I18n\Loader\XliffFileLoader();
        $resourceDir = PROJECT_DIR . '/../vendor/symfony/validator/Resources/translations/';
        foreach (new FilesystemIterator($resourceDir, FilesystemIterator::KEY_AS_FILENAME) as $key => $item) {
            if (!$item->isFile()) {
                continue;
            }
            $lpos = strpos($key, '.');
            $rpos = strrpos($key, '.');
            $locale = substr($key, $lpos + 1, $rpos - $lpos - 1);
            $translator->addResource('xlf', $item->getRealPath(), $locale);
        }
        $translator->addLoader('xlf', $loader);
        return $translator;
    }

    /**
     * _hasKeyPathValue
     *
     * @param array $data data
     * @param string $keyPath key path
     *
     * @return boolean
     */
    private function _hasKeyPathValue($data, $keyPath): bool
    {
        $current = $data;
        $keyArray = explode('>', $keyPath);
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
     * @param array $data data
     * @param ConstraintViolationList $violationList
     * @param string $pkey
     * @return array
     */
    private function _filter(array $data, ConstraintViolationList $violationList, $pkey = null): array
    {
        $errmsg = self::buildErrmsg($violationList, self::initTranslator($this->appConfig));
        foreach ($errmsg as $key => $value) {
            unset($data[$key]);
        }
        $this->_report['error_message'] = array_merge($this->_report['error_message'], ($pkey ? [$pkey => $errmsg] : $errmsg));
        return $data;
    }

    /**
     * buildErrmsg
     *
     * @param ConstraintViolationListInterface $violationList
     * @param Translator $translator
     * @return array
     */
    public static function buildErrmsg(ConstraintViolationListInterface $violationList, Translator
    $translator): array
    {
        $error = [];
        for ($i = 0; $i < $violationList->count(); $i++) {
            $violation = $violationList->get($i);
            $propertyPath = $violation->getPropertyPath();
            $msg = $translator->trans($violation->getMessageTemplate(), $violation->getParameters());
            $offset = 0;
            $property = [];
            while (preg_match('/\[([^]]+)]/', $propertyPath, $matches, PREG_OFFSET_CAPTURE, $offset)) {
                $offset = $matches[0][1] + strlen($matches[0][0]);
                $property[] = $matches[1][0];
            }
            if (empty($property)) {
                $property = [$propertyPath];
            }
            $err = $msg;
            $c = count($property);
            for ($j = 0; $j < $c; $j++) {
                $key = array_pop($property);
                if ($j === $c - 1) {
                    $error[$key] = $err;
                } else {
                    $error = [$key => $err];
                }
            }
        }
        return $error;
    }

}
