<?php

/**
 * Server.php
 *
 * PHP version 7
 *
 * Licensed under the Apache License, Version 2.0 (the "License"),
 * see LICENSE for more details: http://www.apache.org/licenses/LICENSE-2.0.
 *
 * @category PHP
 * @package  LOEYE
 * @author   Zhang Yi <loeyae@gmail.com>
 * @version  2020年1月20日 下午6:38:55
 * @link     https://github.com/loeyae/loeye.git
 */

namespace loeye\database;

/**
 * Server
 *
 * @author   Zhang Yi <loeyae@gmail.com>
 */
class Server
{
    use EntityTrait;
    use RepositoryTrait;

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
     *
     * @var \loeye\base\AppConfig
     */
    protected $appConfig;

    public function __construct(\loeye\base\AppConfig $appConfig, $type = null)
    {
        $this->db = \loeye\base\DB::getInstance($appConfig, $type);
    }

    /**
     * setEntity
     *
     * @param object $entity
     */
    public function setEntity($entity)
    {
        $this->entityClass = $entity;
    }

    /**
     *
     * @return obejct
     */
    public function getEntity()
    {
        return $this->entityClass;
    }

}
