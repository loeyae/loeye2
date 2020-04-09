<?php

/**
 * ExpressionFactory.php
 *
 * PHP version 7
 *
 * Licensed under the Apache License, Version 2.0 (the "License"),
 * see LICENSE for more details: http://www.apache.org/licenses/LICENSE-2.0.
 *
 * @category PHP
 * @package  LOEYE
 * @author   Zhang Yi <loeyae@gmail.com>
 * @version  2020年1月6日 下午3:51:40
 * @link     https://github.com/loeyae/loeye.git
 */

namespace loeye\database;

use Doctrine\Common\Collections\Expr\Comparison;
use Doctrine\Common\Collections\Expr\CompositeExpression;
use Doctrine\Common\Collections\Expr\Expression;
use Doctrine\Common\Collections\ExpressionBuilder;
use loeye\error\DAOException;

/**
 * ExpressionFactory
 *
 * @author   Zhang Yi <loeyae@gmail.com>
 */
class ExpressionFactory {

    public const IS_NULL = 'IS NULL';

    public static $compositeExpressionTypeMapping = [
        CompositeExpression::TYPE_AND => [ExpressionBuilder::class, 'andX'],
        CompositeExpression::TYPE_OR  => [ExpressionBuilder::class, 'orX'],
    ];
    public static $comparisonTypeMapping          = [
        Comparison::EQ          => [ExpressionBuilder::class, 'eq'],
        Comparison::NEQ         => [ExpressionBuilder::class, 'neq'],
        Comparison::GT          => [ExpressionBuilder::class, 'gt'],
        Comparison::LT          => [ExpressionBuilder::class, 'lt'],
        Comparison::GTE         => [ExpressionBuilder::class, 'gte'],
        Comparison::LTE         => [ExpressionBuilder::class, 'lte'],
        Comparison::IN          => [ExpressionBuilder::class, 'in'],
        Comparison::NIN         => [ExpressionBuilder::class, 'notIn'],
        Comparison::CONTAINS    => [ExpressionBuilder::class, 'contains'],
        Comparison::MEMBER_OF   => [ExpressionBuilder::class, 'memberOf'],
        Comparison::STARTS_WITH => [ExpressionBuilder::class, 'startsWith'],
        Comparison::ENDS_WITH   => [ExpressionBuilder::class, 'endsWith'],
    ];

    /**
     * createExpr
     *
     * @param array $data
     * @return Expression|null
     * @throws DAOException
     */
    public static function createExpr(array $data): ?Expression
    {
        if (empty($data)) {
            return null;
        }
        if (isset($data[0])) {
            if (is_array($data[0])) {
                $expires = [];
                foreach ($data as $value) {
                    $expires[] = self::createExpr($value);
                }
                return new CompositeExpression(CompositeExpression::TYPE_AND, $expires);
            }
            $count = count($data);
            if ($count > 2) {
                return self::createComparison($data[0], $data[2], $data[1]);
            }
            if ($count > 1) {
                return self::createExprByKv($data[0], $data[1]);
            }
            throw new DAOException();
        }

        $expires = self::createExprByArray($data);
        return new CompositeExpression(CompositeExpression::TYPE_AND, $expires);
    }

    /**
     *
     * @param array $array
     *
     * @return array
     * @throws DAOException
     */
    public static function createExprByArray(array $array): array
    {
        $exps = [];
        foreach ($array as $key => $value) {
            $exps[] = static::createExprByKv($key, $value);
        }
        return $exps;
    }

    /**
     * createExprByKv
     *
     * @param mixed $key
     * @param mixed $value
     * @return Expression
     * @throws DAOException
     */
    public static function createExprByKv($key, $value): Expression
    {
        if ($value === static::IS_NULL) {
            return new Comparison($key, Comparison::EQ, $value);
        }
        if (is_numeric($key)) {
            throw new DAOException();
        }
        if (array_key_exists(strtoupper($key), static::$compositeExpressionTypeMapping)) {
            return static::createCompositeExpression(strtoupper($key), static::createExpr($value));
        }
        if (is_iterable($value)) {
            return static::createComparison($key, $value, Comparison::IN);
        }

        return static::createComparison($key, $value, Comparison::EQ);
    }

    /**
     *
     * @param string $type
     * @param mixed $expressions
     *
     * @return Expression
     */
    public static function createCompositeExpression($type, $expressions = null): Expression
    {
        return call_user_func(static::$compositeExpressionTypeMapping[$type], $expressions);
    }

    /**
     * createComparison
     *
     * @param string $field
     * @param mixed  $value
     * @param string $operator
     *
     * @return Comparison|null
     */
    public static function createComparison($field, $value, $operator): ?Comparison
    {
        if (array_key_exists($operator, static::$comparisonTypeMapping)) {
            return call_user_func(static::$comparisonTypeMapping[$operator], $field, $value);
        }
        return null;
    }

}
