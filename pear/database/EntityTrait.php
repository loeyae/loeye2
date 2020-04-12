<?php

/**
 * EntityTrait.php
 *
 * PHP version 7
 *
 * Licensed under the Apache License, Version 2.0 (the "License"),
 * see LICENSE for more details: http://www.apache.org/licenses/LICENSE-2.0.
 *
 * @category PHP
 * @package  LOEYE
 * @author   Zhang Yi <loeyae@gmail.com>
 * @version  2020年1月6日 下午2:58:42
 * @link     https://github.com/loeyae/loeye.git
 */

namespace loeye\database;

use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\ORM\TransactionRequiredException;
use loeye\base\DB;
use loeye\base\Utils;
use loeye\error\DataException;
use ReflectionException;

/**
 * EntityTrait
 *
 * @author   Zhang Yi <loeyae@gmail.com>
 */
trait EntityTrait
{
    /**
     *
     * @var DB
     */
    protected $db;

    /**
     *
     * @var string
     */
    protected $entityClass;

    /**
     * insert
     *
     * @param array|Object $data
     *
     * @return Entity|null
     */
    public function insert($data): ?Entity
    {
        try {
            $entity = Utils::source2entity($data, $this->entityClass);
            $this->db->save($entity);
            return $entity;
        } catch (OptimisticLockException $e) {
            \loeye\base\Logger::exception($e);
        } catch (ORMException $e) {
            \loeye\base\Logger::exception($e);
        } catch (ReflectionException $e) {
            \loeye\base\Logger::exception($e);
        }
        return null;
    }

    /**
     * entity
     *
     * @param mixed $id
     *
     * @return Entity|null
     */
    public function get($id): ?Entity
    {
        try {
            return $this->db->entity($this->entityClass, $id);
        } catch (OptimisticLockException $e) {
            \loeye\base\Logger::exception($e);
        } catch (TransactionRequiredException $e) {
            \loeye\base\Logger::exception($e);
        } catch (ORMException $e) {
            \loeye\base\Logger::exception($e);
        }
        return null;
    }

    /**
     *
     * @param mixed $id
     * @param mixed $data
     *
     * @return Entity|null
     */
    public function update($id, $data): ?Entity
    {
        try {
            $entity = $this->get($id);
            Utils::checkNotNull($entity);
            Utils::copyProperties($data, $entity);
            $this->db->save($entity);
            return $entity;
        } catch (OptimisticLockException $e) {
            \loeye\base\Logger::exception($e);
        } catch (ORMException $e) {
            \loeye\base\Logger::exception($e);
        } catch (ReflectionException $e) {
            \loeye\base\Logger::exception($e);
        } catch (DataException $e) {
            \loeye\base\Logger::exception($e);
        }
        return null;
    }

    /**
     * delete
     *
     * @param mixed $id
     *
     * @return boolean
     */
    public function delete($id): bool
    {
        try {
            $entity = $this->get($id);
            Utils::checkNotNull($entity);
            return $this->db->remove($entity);
        } catch (OptimisticLockException | ORMException $e) {
            \loeye\base\Logger::exception($e);
        } catch (DataException $e) {
            \loeye\base\Logger::exception($e);
        }
        return false;
    }

}
