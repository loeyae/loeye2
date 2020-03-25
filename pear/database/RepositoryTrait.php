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

/**
 * RepositoryTrait
 *
 * @author   Zhang Yi <loeyae@gmail.com>
 */
trait RepositoryTrait {

    static $alias = 't';

    /**
     *
     * @var \loeye\base\DB;
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
     * @return object|null
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
     * @param mixed|null $groupBy
     * @param int|null   $start
     * @param int|null   $offset
     *
     * @return array|null
     */
    public function all($criteria = null, $orderBy = null, $start = null, $offset = null): ?array
    {
        if (is_null($criteria)) {
            return $this->db->repository($this->entityClass)->findAll();
        }
        return $this->db->repository($this->entityClass)->findBy($criteria, $orderBy, $offset, $start);
    }


    /**
     *
     * @param mixed $query
     * @param int   $start
     * @param int   $offset
     * @param mixed $orderBy
     * @param mixed $groupBy
     * 
     * @return array|null
     *
     * @throws \loeye\error\BusinessException
     */
    public function page($query, $start = 0, $offset = 10, $orderBy = null, $groupBy = null, $having = null): ?array
    {
        if (is_array($orderBy)) {
            $orderBy = new Expr\OrderBy(...$orderBy);
        }
        if ($query instanceof \Doctrine\ORM\Query) {
            $query->setFirstResult($start)->setMaxResults($offset);
        } else if ($query instanceof \Doctrine\Common\Collections\Criteria) {
            $qb = $this->db->repository($this->entityClass)->createQueryBuilder(static::$alias);
            $qb->setFirstResult($start)->setMaxResults($offset);
            if ($orderBy) {
                $qb->orderBy($orderBy);
            }
            if ($groupBy) {
                $qb->groupBy($groupBy);
            }
            if ($having) {
                $qb->having($having);
            }
            $qb->addCriteria($query)->addSelect(static::$alias);
            $query = $qb->getQuery();
        } else if (is_array($query)) {
            $qb   = $this->db->repository($this->entityClass)->createQueryBuilder(static::$alias);
            $expr = ExpressionFactory::createExpr($query);
            if ($expr) {
                $criteria = \Doctrine\Common\Collections\Criteria::create()->andWhere($expr);
                $qb->addCriteria($criteria);
            }
            $qb->setFirstResult($start)->setMaxResults($offset);
            if ($orderBy) {
                $qb->orderBy($orderBy);
            }
            if ($groupBy) {
                $qb->groupBy($groupBy);
            }
            if ($having) {
                $qb->having($having);
            }
            $query = $qb->getQuery();
        } else if (is_null($query)) {
            $qb = $this->db->repository($this->entityClass)->createQueryBuilder(static::$alias);
            $qb->addSelect(static::$alias)->setFirstResult($start)->setMaxResults($offset);
            if ($orderBy) {
                $qb->orderBy($orderBy);
            }
            if ($groupBy) {
                $qb->groupBy($groupBy);
            }
            if ($having) {
                $qb->having($having);
            }
            $query = $qb->getQuery();
        } else {
            throw new \loeye\error\BusinessException(\loeye\error\BusinessException::INVALID_PARAMETER_MSG, \loeye\error\BusinessException::INVALID_PARAMETER_CODE);
        }
        $paginator = new \Doctrine\ORM\Tools\Pagination\Paginator($query);
        return \loeye\base\Utils::paginator2array($this->db->em(), $paginator);
    }

}
