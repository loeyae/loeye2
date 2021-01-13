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

use Doctrine\Common\Collections\Criteria;
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
     * @param $query
     * @return CompositeExpression|null
     * @throws DAOException
     */
    public static function create($query): ?CompositeExpression
    {
        $expression = self::createExpr($query);
        if ($expression instanceof CompositeExpression) {
            return $expression;
        }
        return $expression ? new CompositeExpression(CompositeExpression::TYPE_AND, [$expression]) : null;
    }

    /**
     * @param CompositeExpression $expression
     * @param $data
     * @return CompositeExpression|null
     */
    public static function filter(CompositeExpression $expression, $data): ?CompositeExpression
    {
        $expressionList = $expression->getExpressionList();
        $filteredExpression = array_filter($expressionList, static function ($item) use ($data) {
            return ($item instanceof Comparison && array_key_exists($item->getField(), $data));
        });
        if ($filteredExpression) {
            return new CompositeExpression($expression->getType(), $filteredExpression);
        }
        return null;
    }

    /**
     * @param CompositeExpression $expression
     * @return array
     */
    public static function toFieldArray(CompositeExpression $expression): array
    {
        $expressionList = $expression->getExpressionList();
        return array_reduce($expressionList, static function($carry, $item) {
            if ($item instanceof Comparison) {
                $carry[$item->getField()] = $item->getValue()->getValue();
            }
            return $carry;
        }, []);
    }

    /**
     * @param Expression|null $expression
     * @return Criteria|null
     */
    public static function toCriteria(Expression $expression = null): ?Criteria
    {
        return $expression ? Criteria::create()->andWhere($expression) : null;
    }

    /**
     * createExpr
     *
     * @param array $data
     * @param string $type
     * @return Expression|null
     * @throws DAOException
     */
    public static function createExpr(array $data, $type = CompositeExpression::TYPE_AND): ?Expression
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
                return new CompositeExpression($type, $expires);
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
        if (count($data) === 1) {
            return self::createExprByKv(key($data), current($data));
        }

        $expires = self::createExprByArray($data);
        return new CompositeExpression($type, $expires);
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
            if ($value !== null) {
                $exps[] = static::createExprByKv($key, $value);
            }
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
            return static::createExpr($value, strtoupper($key));
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
        $callable = static::$compositeExpressionTypeMapping[$type];
        $callable[0] = new ExpressionBuilder();
        return $callable($expressions);
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
            $callable = static::$comparisonTypeMapping[$operator];
            $callable[0] = new ExpressionBuilder();
            return $callable($field, $value);
        }
        return null;
    }

}
