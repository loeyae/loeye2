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
     * @return object
     */
    public function one(array $criteria, $orderBy = nnull)
    {
        return $this->db->one($this->entityClass, $criteria, $orderBy);
    }


    /**
     * all
     *
     * @param array|null $criteria
     * @param mixed|null $orderBy
     * @param mixed|null $groupBy
     * @param int|null   $offset
     * @param int|null   $limit
     *
     * @return array
     */
    public function all($criteria = null, $orderBy = null, $offset = null, $limit = null)
    {
        if (is_null($criteria)) {
            return $this->db->repository($this->entityClass)->findAll();
        }
        return $this->db->repository($this->entityClass)->findBy($criteria, $orderBy, $limit, $offset);
    }


    /**
     *
     * @param type $query
     * @param type $offset
     * @param type $limit
     * @param type $orderBy
     * @param type $groupBy
     * @return type
     *
     * @throws \loeye\error\BusinessException
     */
    public function page($query, $offset = 0, $limit = 10, $orderBy = null, $groupBy = null, $having = null)
    {
        if ($query instanceof \Doctrine\ORM\Query) {
            $query->setFirstResult($offset)->setMaxResults($limit);
        } else if ($query instanceof \Doctrine\Common\Collections\Criteria) {
            $qb = $this->db->repository($this->entityClass)->createQueryBuilder(static::$alias);
            $qb->setFirstResult($offset)->setMaxResults($limit);
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
            $qb->where($expr);
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
            $qb->addSelect(static::$alias)->setFirstResult($offset)->setMaxResults($limit);
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
