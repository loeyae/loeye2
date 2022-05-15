<?php

/**
 * RepositoryTrait.php
 *
 * PHP version 7
 *
 * Licensed under the Apache License, Version 2.0 (the "License"),
 * see LICENSE for more details: http://www.apache.org/licenses/LICENSE-2.0.
 *
 * @category PHP
 * @package  LOEYE
 * @author   Zhang Yi <loeyae@gmail.com>
 * @version  2020年1月6日 下午3:12:29
 * @link     https://github.com/loeyae/loeye.git
 */

namespace loeye\database;

use Doctrine\Common\Collections\Criteria;
use Doctrine\Common\Collections\Expr\Comparison;
use Doctrine\Common\Collections\Expr\CompositeExpression;
use Doctrine\ORM\Query;
use Doctrine\ORM\Query\Expr\OrderBy;
use Doctrine\ORM\Query\QueryException;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator;
use loeye\base\DB;
use loeye\base\Utils;
use loeye\error\BusinessException;
use loeye\error\DAOException;
use ReflectionException;

/**
 * RepositoryTrait
 *
 * @author   Zhang Yi <loeyae@gmail.com>
 */
trait RepositoryTrait
{

    public static $alias = 't';

    /**
     *
     * @var DB;
     */
    protected $db;

    /**
     *
     * @var string
     */
    protected $entityClass;

    /**
     * one
     *
     * @param array $criteria
     * @param mixed $orderBy
     *
     * @return Entity|null
     */
    public function one(array $criteria, $orderBy = null): ?Entity
    {
        return $this->db->one($this->entityClass, $criteria, $orderBy);
    }

    /**
     * all
     *
     * @param array|null $criteria
     * @param mixed|null $orderBy
     * @param int|null $start
     * @param int|null $offset
     *
     * @return array|null
     */
    public function all($criteria = null, $orderBy = null, $start = null, $offset = null): ?array
    {
        if ($offset === null) {
            $offset = DATABASE_PAGE_DEFAULT_LIMIT;
        }
        if ($criteria === null) {
            return $this->db->repository($this->entityClass)->findBy([], $orderBy, $offset, $start);
        }
        return $this->db->repository($this->entityClass)->findBy($criteria, $orderBy, $offset, $start);
    }
    /**
     * query
     *
     * @param $criteria
     * @param $orderBy
     * @param $groupBy
     * @param $having
     * @param $start
     * @param $offset
     * @return array|float|int|string
     * @throws BusinessException
     * @throws DAOException
     * @throws QueryException
     */
    public function query($criteria = null, $orderBy = null, $groupBy = null, $having = null, $start = null, $offset = null)
    {
        $qb = $this->buildQuery($criteria, $orderBy, $groupBy, $having);
        $query = $qb->getQuery();
        if ($start !== null) {
            $query->setFirstResult($start);
        }
        if ($offset !== null) {
            $query->setMaxResults($offset);
        }

        return $query->getArrayResult();
    }

    /**
     * page
     *
     * @param mixed $query
     * @param int $start
     * @param int $offset
     * @param mixed $orderBy
     * @param mixed $groupBy
     *
     * @param null $having
     * @return Paginator
     *
     * @throws QueryException
     * @throws ReflectionException
     * @throws BusinessException
     * @throws DAOException
     */
    public function page($query, $start = 0, $offset = 10, $orderBy = null, $groupBy = null, $having = null): ?Paginator
    {
        if ($query instanceof Query) {
            $query->setFirstResult($start)->setMaxResults($offset);
        } else {
            $qb = $this->buildQuery($query, $orderBy, $groupBy, $having);
            $query = $qb->getQuery();
            $query->setFirstResult($start);
            $query->setMaxResults($offset);
        }
        return new Paginator($query);
    }

    /**
     * buildQuery
     *
     * @param $query
     * @param $orderBy
     * @param $groupBy
     * @param $having
     * @return QueryBuilder
     * @throws BusinessException
     * @throws DAOException
     * @throws QueryException
     */
    protected function buildQuery($query, $orderBy = null, $groupBy = null, $having = null): QueryBuilder
    {
        $qb = $this->db->repository($this->entityClass)->createQueryBuilder(static::$alias);
        if ($query instanceof Criteria) {
            $this->parseOrderBy($qb, $orderBy);
            $this->parseGroupBy($qb, $groupBy);
            $this->parseHaving($qb, $having);
            $qb->addCriteria($query)->addSelect(static::$alias);
        } else if (is_array($query)) {
            $expr = ExpressionFactory::createExpr($query);
            if ($expr) {
                $criteria = Criteria::create()->andWhere($expr);
                $qb->addCriteria($criteria);
            }
            $this->parseOrderBy($qb, $orderBy);
            $this->parseGroupBy($qb, $groupBy);
            $this->parseHaving($qb, $having);
        } else if ($query === null) {
            $qb->addSelect(static::$alias);
            $this->parseOrderBy($qb, $orderBy);
            $this->parseGroupBy($qb, $groupBy);
            $this->parseHaving($qb, $having);
        } else {
            throw new BusinessException(BusinessException::INVALID_PARAMETER_MSG, BusinessException::INVALID_PARAMETER_CODE);
        }
        return $qb;
    }

    /**
     *
     * @param QueryBuilder $qb QueryBuilder
     * @param mixed $orderBy orderBy
     *
     * @return QueryBuilder
     */
    protected function parseOrderBy(QueryBuilder $qb, $orderBy): QueryBuilder
    {
        if ($orderBy) {
            $expr = new OrderBy();
            if (is_array($orderBy)) {
                if (isset($orderBy[0])) {
                    $expr->add(self::$alias . '.' . $orderBy[0], $orderBy[1] ?? null);
                } else {
                    foreach ($orderBy as $key => $value) {
                        $expr->add(self::$alias . '.' . $key, $value);
                    }
                }
            } else {
                $expr->add(self::$alias . '.' . $orderBy);
            }
            $qb->orderBy($expr);
        }
        return $qb;
    }

    /**
     * parseGroupBy
     *
     * @param QueryBuilder $qb QueryBuilder
     * @param mixed $groupBy groupBy
     * @return QueryBuilder
     */
    protected function parseGroupBy(QueryBuilder $qb, $groupBy): QueryBuilder
    {
        if ($groupBy) {
            if (is_array($groupBy)) {
                $qb->groupBy(implode(', ', array_map(static function($item){
                    return self::$alias .'.'. $item;
                }, $groupBy)));
            } else {
                $qb->groupBy(self::$alias . '.' . $groupBy);
            }
        }
        return $qb;
    }

    /**
     * parseHaving
     *
     * @param QueryBuilder $qb QueryBuilder
     * @param mixed $having having
     * @return QueryBuilder
     * @throws DAOException
     */
    protected function parseHaving(QueryBuilder $qb, $having): QueryBuilder
    {
        if ($having) {
            $object = null;
            $expr = ExpressionFactory::createExpr($having);
            if ($expr instanceof CompositeExpression) {
                $type = $expr->getType();
                $exprList = $expr->getExpressionList();
                if ($type === CompositeExpression::TYPE_AND) {
                    $object = new Query\Expr\Andx();
                } else {
                    $object = new Query\Expr\Orx();
                }
                foreach ($exprList as $key => $item) {

                    if ($item instanceof Comparison) {
                        $object->add(new Query\Expr\Comparison(self::$alias . '.' . htmlentities($item->getField()),
                            $item->getOperator(), ':having_'.$key));

                        $qb->setParameter('having_'.$key, $item->getValue()->getValue());
                    }
                }
            } else if ($expr instanceof Comparison){
                $object = new Query\Expr\Comparison(self::$alias . '.' . htmlentities($expr->getField()),
                        $expr->getOperator(), ':having_0');
                $qb->setParameter('having_0', $expr->getValue()->getValue());
            }
            $qb->having($object);
        }
        return $qb;
    }

}
