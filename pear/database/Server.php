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

use loeye\base\AppConfig;
use loeye\base\DB;
use loeye\base\Exception;

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
     * @var DB;
     */
    protected $db;

    /**
     *
     * @var string
     */
    protected $entityClass;

    /**
     *
     * @var AppConfig
     */
    protected $appConfig;

    public function __construct(AppConfig $appConfig, $type = null, $singleConnection = true)
    {
        if ($singleConnection) {
            try {
                $this->db = DB::getInstance($appConfig, $type, is_bool($singleConnection) ? null : (string)$singleConnection);
            } catch (Exception $e) {
                \loeye\base\Logger::exception($e);
            }
        } else {
            try {
                $this->db = new DB($appConfig, $type);
            } catch (Exception $e) {
                \loeye\base\Logger::exception($e);
            }
        }
    }

    /**
     * setEntity
     *
     * @param object $entity
     */
    final public function setEntity($entity)
    {
        $this->entityClass = $entity;
    }

    /**
     *
     * @return obejct
     */
    final public function getEntity()
    {
        return $this->entityClass;
    }

}
