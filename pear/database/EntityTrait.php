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

/**
 * EntityTrait
 *
 * @author   Zhang Yi <loeyae@gmail.com>
 */
trait EntityTrait
{
    /**
     *
     * @var \loeye\base\DB
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
     * @return obejct
     */
    public function insert($data): ?Entity
    {
        $entity = \loeye\base\Utils::source2entity($data, $this->entityClass);
        $this->db->save($entity);
        return $entity;
    }

    /**
     * entity
     *
     * @param mixed $id
     *
     * @return obejct|null
     */
    public function get($id): ?Entity
    {
        return $this->db->entity($this->entityClass, $id);
    }

    /**
     *
     * @param mixed $id
     * @param mixed $data
     *
     * @return type
     */
    public function update($id, $data): Entity
    {
        $entity = $this->get($id);
        \loeye\base\Utils::checkNotNull($entity);
        \loeye\base\Utils::copyProperties($data, $entity);
        $this->db->save($entity);
        return $entity;
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
        $entity = $this->get($id);
        \loeye\base\Utils::checkNotNull($entity);
        return $this->db->remove($entity);
    }

}
