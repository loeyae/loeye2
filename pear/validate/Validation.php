<?php

/**
 * Validation.php
 *
 * PHP version 7
 *
 * Licensed under the Apache License, Version 2.0 (the "License"),
 * see LICENSE for more details: http://www.apache.org/licenses/LICENSE-2.0.
 *
 * @category PHP
 * @package  LOEYE
 * @author   Zhang Yi <loeyae@gmail.com>
 * @version  2020年3月4日 下午9:15:45
 * @link     https://github.com/loeyae/loeye.git
 */

namespace loeye\validate;


use Generator;
use loeye\base\Utils;
use loeye\error\ValidateError;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Validator\ValidatorBuilder;

/**
 * Validation
 *
 * @author   Zhang Yi <loeyae@gmail.com>
 */
final class Validation
{

    /**
     * Creates a new validator.
     *
     * If you want to configure the validator, use
     * {@link createValidatorBuilder()} instead.
     */
    public static function createValidator(): ValidatorInterface
    {
        \Doctrine\Common\Annotations\AnnotationRegistry::registerLoader(static function ($class) {
            return class_exists($class);
        });
        return self::createValidatorBuilder()->enableAnnotationMapping()->getValidator();
    }

    /**
     * Creates a configurable builder for validator objects.
     */
    public static function createValidatorBuilder(): ValidatorBuilder
    {
        return new ValidatorBuilder();
    }

    /**
     * This class cannot be instantiated.
     */
    private function __construct()
    {
    }


    /**
     * @param array $data
     * @param string $entityClass
     * @param array $filterRule
     * @param null $group
     * @return object
     * @throws ReflectionException
     * @throws ValidateError
     */
    public static function validate(array $data, string $entityClass, array $filterRule, $group = null)
    {
        $entity = Utils::source2entity(self::filterData($data, $filterRule), $entityClass, true);
        $violationList = self::createValidator()->validate($entity, null, $group);
        if ($violationList->count() > 0) {
            $validateError = Validator::buildErrmsg($violationList, Validator::initTranslator());
            throw new ValidateError($validateError, ValidateError::DEFAULT_ERROR_MSG,
                ValidateError::DEFAULT_ERROR_CODE);
        }
        return $entity;
    }


    /**
     * @param array $data
     * @param array $filterRule
     * @return Generator|null
     */
    public static function filterData(array $data, array $filterRule): ?Generator
    {
        foreach ($data as $key => $datum) {
            yield $key => self::filterVar($datum, $filterRule[$key] ?? []);
        }
    }

    /**
     * filterVar
     *
     * @param $var
     * @param array $rule
     * @return array|mixed
     */
    public static function filterVar($var, $rule = [])
    {
        if (is_iterable($var)) {
            $filtered = [];
            foreach ($var as $key => $value) {
                $filtered[$key] = self::filterVar($value, $rule);
            }
            return $filtered;
        }
        $filter = self::getFilter($var, $rule);
        $ops = [];
        $ops['flag'] = self::getFlag($rule, null);
        !isset($rule['filter']['options']) ?: $ops['options'] = $rule['filter']['options'];
        $validated = filter_var($var, $filter, $ops);
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
     * @param $rule
     * @param null $default
     * @return mixed|null
     */
    protected static function getFlag($rule, $default = null)
    {
        if (isset($rule['filter']['flag'])) {
            $flag = $rule['filter']['flag'];
            if (is_int($flag)) {
                return $flag;
            }
            if (defined($flag)) {
                return constant($flag);
            }
        }
        return $default;
    }

    /**
     * @param $var
     * @param $rule
     * @return int|mixed
     */
    protected static function getFilter($var, $rule)
    {
        if (isset($rule['filter']['type'])) {
            $filter = $rule['filter']['type'];
            if (is_int($filter)) {
                return $filter;
            }
            if (defined($filter)) {
                return constant($filter);
            }
        }
        return self::getDefaultFilter($var);
    }

    /**
     * @param $var
     * @return int
     */
    public static function getDefaultFilter($var): int
    {
        if (is_int($var)) {
            return FILTER_VALIDATE_INT;
        }
        if (is_numeric($var)) {
            return FILTER_VALIDATE_FLOAT;
        }
        return FILTER_SANITIZE_STRING;
    }
}
