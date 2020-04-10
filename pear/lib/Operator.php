<?php

/**
 * Operator.php
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

/**
 * Operator
 *
 * @author   Zhang Yi <loeyae@gmail.com>
 */
class Operator
{

    static protected $preoperator = array('!');
    static protected $logic = array(
        '&&',
        '\|\|',
    );
    static protected $operator = array(
        '=',
        '>',
        '>=',
        '<',
        '<=',
        '==',
        '!=',
        '!==',
        ' instanceof ',
        ' in ',
    );

    /**
     * getPattern
     *
     * @return string
     */
    public static function getPattern(): string
    {
        $logic = implode('|', self::$logic);
        $operator = implode('|', self::$operator);
        $preOperator = implode('|', self::$preoperator);

        $pattern = '(' . $preOperator . ')?\s?';
        $pattern .= '(?:([$_:\w\[\.\]]+)(?:\s?(' . $operator . ')\s?([^' . $logic . ']+))?)';
        return '/(?:(' . $logic . ')\s)?' . $pattern . '/';
    }

    /**
     * operate
     *
     * @param string $expression expression
     *
     * @return bool
     */
    public static function operate($expression): bool
    {
        $pattern = static::getPattern();
        $matches = array();
        $offset = 0;
        $result = false;
        while (preg_match($pattern, $expression, $matches, PREG_OFFSET_CAPTURE, $offset)) {
            $logicOperator = $matches[1][0] or $logicOperator = '||';
            $subject1 = (!empty($matches[3])) ? $matches[3][0] : null;
            $operator = (!empty($matches[4])) ? $matches[4][0] : null;
            $subject2 = (!empty($matches[5])) ? $matches[5][0] : null;
            $preOperator = (!empty($matches[2])) ? $matches[2][0] : null;
            $current = self::execute($subject1, $operator, $subject2, $preOperator);
            $result = static::logicOperation($result, $logicOperator, $current);
            $end = end($matches);
            $offset = $end[1] + mb_strlen($end[0]);
        }
        return $result;
    }

    /**
     * match
     *
     * @param string $pattern pattern
     * @param string $subject subject
     *
     * @return boolean|array
     */
    public static function match($pattern, $subject)
    {
        $matches = array();
        if (preg_match($pattern, $subject, $matches)) {
            return $matches;
        }
        return false;
    }

    /**
     * execute
     *
     * @param string $subject1 subject
     * @param string $operator operator
     * @param string $subject2 subject
     * @param string $preOperator pre operator
     *
     * @return boolean
     */
    public static function execute(
        $subject1, $operator = null, $subject2 = null, $preOperator = null
    ): bool
    {
        if ($operator === null && $subject2 === null) {
            $return = ($subject1 === true);
        } else if (in_array($operator, self::$operator, true)) {
            $return = self::_operation($subject1, $operator, $subject2);
        } else {
            return false;
        }
        if (in_array($preOperator, self::$preoperator, true)) {
            return self::_preOperation($preOperator, $return);
        }
        return $return;
    }

    /**
     * logicOperation
     *
     * @param string $subject1 subject
     * @param string $operator operator
     * @param string $subject2 subject
     *
     * @return bool
     */
    public static function logicOperation($subject1, $operator, $subject2): bool
    {
        if ($operator === '||') {
            $return = ($subject1 || $subject2);
        } else {
            $return = ($subject1 && $subject2);
        }
        return $return;
    }

    /**
     * _operation
     *
     * @param string|null $subject1 subject
     * @param string|null $operator operator
     * @param string|null $subject2 subject
     *
     * @return boolean
     */
    private static function _operation($subject1, $operator, $subject2): bool
    {
        if ($subject1 === 'null') {
            $subject1 = null;
        }
        if ($subject2 === 'null') {
            $subject2 = null;
        }
        switch (trim($operator)) {
            case '==':
                $return = ($subject1 === $subject2);
                break;
            case '>':
                $return = ($subject1 > $subject2);
                break;
            case '<':
                $return = ($subject1 < $subject2);
                break;
            case '>=':
                $return = ($subject1 >= $subject2);
                break;
            case '<=':
                $return = ($subject1 <= $subject2);
                break;
            case '!=':
                $return = ($subject1 != $subject2);
                break;
            case '!==':
                $return = ($subject1 !== $subject2);
                break;
            case 'in':
                $subject3 = explode(',', $subject2);
                $return = in_array($subject1, $subject3, true);
                break;
            case 'instanceof':
                $return = ($subject1 instanceof $subject2);
                break;
            default:
                $return = ($subject1 == $subject2);
                break;
        }
        return $return;
    }

    /**
     * pre operation
     *
     * @param string $preOperator pre operator
     * @param mixed $subject subject
     *
     * @return boolean
     */
    private static function _preOperation($preOperator, $subject): bool
    {
        $return = false;
        if ($preOperator) {
            $return = !$subject;
        }
        return $return;
    }

}
