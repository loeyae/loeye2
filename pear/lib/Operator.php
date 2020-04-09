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
    public static function getPattern()
    {
        $logic       = implode('|', self::$logic);
        $operator    = implode('|', self::$operator);
        $preoperator = implode('|', self::$preoperator);

        $pattern = '(' . $preoperator . ')?\s?';
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
    public static function operate($expression)
    {
        $pattern = LoeyeOperator::getPattern();
        $matches = array();
        $offset  = 0;
        $result  = false;
        while (preg_match($pattern, $expression, $matches, PREG_OFFSET_CAPTURE, $offset)) {
            $logicOperator = $matches[1][0] or $logicOperator = '||';
            $subject1      = (!empty($matches[3])) ? $matches[3][0] : null;
            $operator      = (!empty($matches[4])) ? $matches[4][0] : null;
            $subject2      = (!empty($matches[5])) ? $matches[5][0] : null;
            $preoperator   = (!empty($matches[2])) ? $matches[2][0] : null;
            $current       = self::excute($subject1, $operator, $subject2, $preoperator);
            $result        = LoeyeOperator::logicOperation($result, $logicOperator, $current);
            $end           = end($matches);
            $offset        = $end[1] + mb_strlen($end[0]);
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
     * excute
     *
     * @param string $subject1    subject
     * @param string $operator    operator
     * @param string $subject2    subject
     * @param string $preoperator preoperator
     *
     * @return boolean
     */
    public static function excute(
            $subject1, $operator = null, $subject2 = null, $preoperator = null
    )
    {
        if ($operator == null && $subject2 == null) {
            $return = ($subject1 == true);
        } else if (in_array($operator, self::$operator)) {
            $return = self::_operation($subject1, $operator, $subject2);
        } else {
            return false;
        }
        if (in_array($preoperator, self::$preoperator)) {
            return self::_preoperation($preoperator, $return);
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
    public static function logicOperation($subject1, $operator, $subject2)
    {
        switch ($operator) {
            case '||':
                $return = ($subject1 || $subject2);
                break;
            default :
                $return = ($subject1 && $subject2);
        }
        return $return;
    }

    /**
     * _operation
     *
     * @param string $subject1 subject
     * @param string $operator operator
     * @param string $subject2 subject
     *
     * @return boolean
     */
    static private function _operation($subject1, $operator, $subject2)
    {
        if ($subject1 === 'null') {
            $subject1 = null;
        }
        if ($subject2 === 'null') {
            $subject2 = null;
        }
        switch (trim($operator)) {
            case '==':
                $return   = ($subject1 === $subject2);
                break;
            case '>':
                $return   = ($subject1 > $subject2);
                break;
            case '<':
                $return   = ($subject1 < $subject2);
                break;
            case '>=':
                $return   = ($subject1 >= $subject2);
                break;
            case '<=':
                $return   = ($subject1 <= $subject2);
                break;
            case '!=':
                $return   = ($subject1 != $subject2);
                break;
            case '!==':
                $return   = ($subject1 !== $subject2);
                break;
            case 'in':
                $subject2 = explode(',', $subject2);
                $return   = in_array($subject1, $subject2);
                break;
            case 'instanceof':
                $return   = ($subject1 instanceof $subject2);
                break;
            default:
                $return   = ($subject1 == $subject2);
                break;
        }
        return $return;
    }

    /**
     * _preoperation
     *
     * @param string $preoperator preoperator
     * @param string $subject     subject
     *
     * @return boolean
     */
    static private function _preoperation($preoperator, $subject)
    {
        switch ($preoperator) {
            default:
                $return = !$subject;
                break;
        }
        return $return;
    }

}
