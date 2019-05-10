<?php

/**
 * SampleServer.php
 *
 * Licensed under the Apache License, Version 2.0 (the "License"),
 * see LICENSE for more details: http://www.apache.org/licenses/LICENSE-2.0.
 *
 * @author  Zhang Yi <loeyae@gmail.com>
 * @version 2019-02-25 17:39:00
 */
namespace app\services\server;
/**
 * SampleServer
 *
 * @author   Zhang Yi <loeyae@gmail.com>
 */
class SampleServer extends AbstractServer
{

    protected $db;
    protected $entity;

    /**
     * init
     *
     * @return void
     */
    protected function init()
    {
        $this->db = \loeye\base\DB::getInstance($this->config);
        $this->entity = \app\models\entity\User::class;
    }

    /**
     * getUser
     *
     * @param type $id
     *
     * @return \app\models\entity\User
     */
    public function getUser($id)
    {
        return $this->db->entity($this->entity, $id);
    }

    /**
     * listUser
     *
     * @param int $id
     *
     * @return array
     */
    public function listUser($id = 0)
    {
        $qb = $this->db->createQueryBuilder()->select('u')->from($this->entity, 'u');
        $exr = $qb->expr()->gte('u.id', $id);
        $qb->where($exr)->setMaxResults(10);
        $query = $qb->getQuery();
        return $query->getArrayResult();
    }

}
