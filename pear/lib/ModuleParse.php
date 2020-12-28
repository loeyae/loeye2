<?php

/**
 * ModuleParse.php
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

namespace loeye\lib;

use loeye\base\Context;

/**
 * Description of ModuleParse
 *
 * @author   Zhang Yi <loeyae@gmail.com>
 */
class ModuleParse
{

    public const MAGIC_VAR_REGEX_PATTERN = '/^\$_(GET|POST|REQUEST|SERVER|COOKIE|SESSION|CONST|CONTEXT|ENV|FILES)\[(.*?)\]$/';
    public const MAGIC_FUNC_REGEX_PATTERN = '/^__([a-z]\w*)\[(.*?)\]$/';
    public const CONDITION_KEY = 'if';
    public const PARALLEL_KEY = 'parallel';

    /**
     * parseInput
     *
     * @param mixed $input input
     * @param Context $context context
     *
     * @return mixed
     */
    public static function parseInput($input, Context $context)
    {
        if (is_array($input)) {
            foreach ($input as $key => $value) {
                $key = (string)(static::parseInput($key, $context));
                if ($key === '') {
                    continue;
                }
                $input[$key] = static::parseInput($value, $context);
            }
        } else {
            $input = trim($input);
            $matches = array();
            if (preg_match(self::MAGIC_VAR_REGEX_PATTERN, $input, $matches)) {
                $matchedKey = $matches[1] ?? null;
                $matchedValue = $matches[2] ?? null;
                if (!empty($matchedKey) && !empty($matchedValue)) {
                    $valueList = explode('.', $matchedValue);
                    $matchedValue = array_shift($valueList);
                    $input = null;
                    switch ($matchedKey) {
                        case 'CONTEXT':
                            $input = $context->get($matchedValue);
                            break;
                        case 'CONST':
                            $const = $matchedValue;
                            if (defined($const)) {
                                $input = constant($const);
                            }
                            break;
                        case 'SESSION':
                            $input = $context->getRequest()->getSession()->get($matchedValue);
                            break;
                        case 'REQUEST':
                            $input = $context->getRequest()->getParameter($matchedValue);
                            break;
                        case 'GET':
                            $input = $context->getRequest()->query->get($matchedValue);
                            break;
                        case 'POST':
                            $input = $context->getRequest()->request->get($matchedValue);
                            break;
                        case 'COOKIE':
                            $input = $context->getRequest()->cookies->get($matchedValue);
                            break;
                        case 'HEADER':
                            $input = $context->getRequest()->headers->get($matchedValue);
                            break;
                        case 'PATH':
                            $input = $context->getRequest()->getPathVariable($matchedValue);
                            break;
                        case 'ENV':
                            $input = $context->getRequest()->getEnv($matchedValue);
                            break;
                        case 'FILES':
                            $input = $context->getRequest()->files->get($matchedValue);
                            break;
                        default :
                            $input = $context->getRequest()->server->get($matchedValue) ??
                                $_SERVER[$matchedValue] ?? null;
                            break;
                    }
                    if (!empty($valueList)) {
                        foreach ($valueList as $value) {
                            $input = $input[$value] ?? null;
                        }
                    }
                }
            } else if (preg_match(self::MAGIC_FUNC_REGEX_PATTERN, $input, $matches)) {
                $func = $matches[1] ?? null;
                $argList = $matches[2] ?? '';
                $parameter = explode(',', $argList);
                if (!empty($func) && method_exists(FuncLibraries::class, $func)) {
                    $input = FuncLibraries::$func($context, $parameter);
                }
            }
        }
        return $input;
    }

    /**
     * isParallel
     *
     * @param string $key key
     *
     * @return boolean
     */
    public static function isParallel($key): bool
    {
        return static::PARALLEL_KEY === $key;
    }

    /**
     * isCondition
     *
     * @param string $key key
     *
     * @return boolean
     */
    public static function isCondition($key): bool
    {
        $key = trim($key);
        $prefix = mb_substr($key, 0, 2);
        return $prefix === self::CONDITION_KEY;
    }

    /**
     * groupConditionResult
     *
     * @param string $condition condition
     * @param Context $context context
     *
     * @return boolean
     */
    public static function groupConditionResult($condition, Context $context): bool
    {
        $condition = trim($condition);
        $matches = Operator::match('/^if\s?\(?([^\)]+)\)?\s?$/', $condition);
        if ($matches === false) {
            return false;
        }
        $expression = $matches[1];
        return self::conditionResult($expression, $context);
    }

    /**
     * conditionResult
     *
     * @param string $condition condition
     * @param Context $context context
     *
     * @return boolean
     */
    public static function conditionResult($condition, Context $context): bool
    {
        $pattern = Operator::getPattern();
        $matches = array();
        $offset = 0;
        $result = false;
        while (preg_match($pattern, $condition, $matches, PREG_OFFSET_CAPTURE, $offset)) {
            $logicOperator = $matches[1][0] or $logicOperator = '||';
            $current = static::_getExpressionResult($matches, $context);
            $result = Operator::logicOperation($result, $logicOperator, $current);
            $end = end($matches);
            $offset = $end[1] + mb_strlen($end[0]);
        }
        return $result;
    }

    /**
     * _getExpressionResult
     *
     * @param array $matches matches
     * @param Context $context context
     *
     * @return bool
     */
    private static function _getExpressionResult($matches, Context $context): bool
    {
        $preOperator = empty($matches[2][0]) ? null : $matches[2][0];
        $subject1 = empty($matches[3][0]) ? $matches[3][0] : self::parseInput($matches[3][0], $context);
        $operator = empty($matches[4][0]) ? null : $matches[4][0];
        $subject2 = (!isset($matches[5][0]) || $matches[5][0] === '') ? null : self::parseInput($matches[5][0],
            $context);
        return Operator::execute($subject1, $operator, $subject2, $preOperator);
    }

}
