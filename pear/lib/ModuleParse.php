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
 * @version  GIT: $Id: Zhang Yi $
 * @link     https://github.com/loeyae/loeye.git
 */

namespace loeye\lib;

/**
 * Description of ModuleParse
 *
 * @author   Zhang Yi <loeyae@gmail.com>
 */
class ModuleParse
{

    const MAGIC_VAR_REGEX_PATTERN = '/^\$_(GET|POST|REQUEST|SERVER|COOKIE|SESSION|CONST|CONTEXT)\[(.*?)\]$/';
    const MAGIC_FUNC_REGEX_PATTERN = '/^__([a-z]\w*)\[([^\)]*?)\]$/';
    const CONDITION_KEY = 'if';
    const PARALLEL_KEY = 'parallel';

    /**
     * parseInput
     *
     * @param mixed               $input   input
     * @param \loeye\base\Context $context context
     *
     * @return mixed
     */
    static public function parseInput($input, \loeye\base\Context $context)
    {
        if (is_array($input)) {
            foreach ($input as $key => $value) {
                $key = (string) (static::parseInput($key, $context));
                if ($key == '') {
                    continue;
                }
                $input[$key] = static::parseInput($value, $context);
            }
        } else {
            $input   = trim($input);
            $matches = array();
            if (preg_match(self::MAGIC_VAR_REGEX_PATTERN, $input, $matches)) {
                $matcheKey   = isset($matches[1]) ? $matches[1] : null;
                $matcheValue = isset($matches[2]) ? $matches[2] : null;
                if (!empty($matcheKey) && !empty($matcheValue)) {
                    $valueList   = explode('.', $matcheValue);
                    $matcheValue = array_shift($valueList);
                    $input       = null;
                    switch ($matcheKey) {
                        case 'CONTEXT':
                            $input = $context->get($matcheValue);
                            break;
                        case 'CONST':
                            $const = $matcheValue;
                            if (defined($const)) {
                                $input = constant($const);
                            }
                            break;
                        case 'REQUEST':
                            if (isset($_REQUEST[$matcheValue])) {
                                $input = $_REQUEST[$matcheValue];
                            }
                            break;
                        case 'SESSION':
                            if (isset($_SESSION[$matcheValue])) {
                                $input = $_SESSION[$matcheValue];
                            }
                            break;
                        default :
                            $inputType = 'INPUT_' . $matcheKey;
                            $input     = filter_input(constant($inputType),
                                    $matcheValue, FILTER_SANITIZE_STRING);
                            break;
                    }
                    if (!empty($valueList)) {
                        foreach ($valueList as $value) {
                            if (is_array($input) && isset($input[$value])) {
                                $input = $input[$value];
                            } else {
                                $input = null;
                            }
                        }
                    }
                }
            } else if (preg_match(self::MAGIC_FUNC_REGEX_PATTERN, $input, $matches)) {
                $func      = isset($matches[1]) ? $matches[1] : null;
                $argList   = isset($matches[2]) ? $matches[2] : '';
                $parameter = explode(',', $argList);
                if (!empty($func) && method_exists(\loeye\lib\FuncLibraries, $func)) {
                    $input = \loeye\lib\FuncLibraries::$func($context, $parameter);
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
    static public function isParallel($key)
    {
        if (static::PARALLEL_KEY === $key) {
            return true;
        }
        return false;
    }

    /**
     * isCondition
     *
     * @param string $key key
     *
     * @return boolean
     */
    static public function isCondition($key)
    {
        $key    = trim($key);
        $prefix = mb_substr($key, 0, 2);
        return $prefix == self::CONDITION_KEY;
    }

    /**
     * groupConditionResult
     *
     * @param string              $condition condition
     * @param \loeye\base\Context $context   context
     *
     * @return boolean
     */
    static public function groupConditionResult($condition, \loeye\base\Contextt $context)
    {
        $condition = trim($condition);
        $matches   = \loeye\lib\Operator::match('/^if\s?\(?([^\)]+)\)?\s?$/', $condition);
        if ($matches == false) {
            return false;
        }
        $expression = $matches[1];
        $pattern    = \loeye\lib\Operator::getPattern();
        $matches    = array();
        $offset     = 0;
        $result     = false;
        while (preg_match($pattern, $expression, $matches, PREG_OFFSET_CAPTURE, $offset)) {
            $logicOperator = $matches[1][0] or $logicOperator = '||';
            $current       = static::_getExpressionResult($matches, $context);
            $result        = \loeye\lib\Operator::logicOperation($result, $logicOperator, $current);
            $end           = end($matches);
            $offset        = $end[1] + mb_strlen($end[0]);
        }
        return $result;
    }

    /**
     * conditionResult
     *
     * @param string              $condition condition
     * @param \loeye\base\Context $context   context
     *
     * @return boolean
     */
    static public function conditionResult($condition, \loeye\base\Context $context)
    {
        $pattern = \loeye\lib\Operator::getPattern();
        $matches = array();
        $offset  = 0;
        $result  = false;
        while (preg_match($pattern, $condition, $matches, PREG_OFFSET_CAPTURE, $offset)) {
            $logicOperator = $matches[1][0] or $logicOperator = '||';
            $current       = static::_getExpressionResult($matches, $context);
            $result        = \loeye\lib\Operator::logicOperation($result, $logicOperator, $current);
            $end           = end($matches);
            $offset        = $end[1] + mb_strlen($end[0]);
        }
        return $result;
    }

    /**
     * _getExpressionResult
     *
     * @param array               $matches matches
     * @param \loeye\base\Context $context context
     *
     * @return bool
     */
    static private function _getExpressionResult($matches, \loeye\base\Context $context)
    {
        $preoperator = empty($matches[2][0]) ? null : $matches[2][0];
        $subject1    = empty($matches[3][0]) ? $matches[3][0] : self::parseInput($matches[3][0], $context);
        $operator    = empty($matches[4][0]) ? null : $matches[4][0];
        $subject2    = (!isset($matches[5][0]) || $matches[5][0] == '') ? null : self::parseInput($matches[5][0], $context);
        return \loeye\lib\Operator::excute($subject1, $operator, $subject2, $preoperator);
    }

}
