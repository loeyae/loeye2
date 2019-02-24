<?php

/**
 * DAO.php
 *
 * PHP version 7
 *
 * Licensed under the Apache License, Version 2.0 (the "License"),
 * see LICENSE for more details: http://www.apache.org/licenses/LICENSE-2.0.
 *
 * @category PHP
 * @package  LOEYE
 * @author   Zhang Yi <loeyae@gmail.com>
 * @version  GIT: $Id: Zhang Yi $
 * @link     https://github.com/loeyae/loeye.git
 */

namespace loeye\base;

/**
 * Description of DB
 *
 * @author   Zhang Yi <loeyae@gmail.com>
 */
class DB
{

    use \loeye\std\ConfigTrait;

    const BUNDLE = 'database';

    /**
     *
     * @var \Doctrine\ORM\EntityManager
     */
    protected $em;
    protected $defaultType;
    protected $isDevMode = false;
    protected static $_instance;

    /**
     * __construct
     *
     * @param \loeye\base\AppConfig $appConfig AppConfig
     * @param string|null           $type      type
     */
    public function __construct(AppConfig $appConfig, $type = null)
    {
        $property = $appConfig->getPropertyName();
        $settins  = $appConfig->getSetting('application.database');
        $config   = $this->propertyConfig($property, static::BUNDLE);
        if (is_string($settins)) {
            $this->defaultType = $settins;
        } else {
            $this->defaultType = $settins['default'] ?? null;
            $this->isDevMode   = $settins['is_dev_mode'] ?? false;
        }
        $this->_getEntityManager($config, $type);
    }

    /**
     * getInstance
     *
     * @param \loeye\base\AppConfig $appConfig AppConfig
     * @param string|null           $type      type
     *
     * @return self
     */
    static public function getInstance(AppConfig $appConfig, $type = null)
    {
        $it = ($type ?? 'default');
        if (!isset(self::$_instance[$it])) {
            self::$_instance[$it] = new self($appConfig, $type);
        }
        return self::$_instance[$it];
    }

    /**
     * _getEntityManager
     *
     * @param \loeye\base\Configuration $config   Configuration
     * @param string                    $property property
     * @param string                    $type     type
     *
     * @return void
     * @throws Exception
     */
    private function _getEntityManager(Configuration $config, $type)
    {
        $key = $type ?? $this->defaultType;
        if (!$key) {
            throw new Exception('Invalid database type.');
        }
        $dbSetting = $config->get($key);
        if (!$dbSetting) {
            throw new Exception('Invalid database setting.');
        }
        $this->em = \loeye\database\EntityManager::getManager($dbSetting);
    }

    /**
     * entityManager
     *
     * @return \Doctrine\ORM\EntityManager
     */
    public function entityManager()
    {
        return $this->em;
    }

    public function createQueryBuilder()
    {
        return $this->em->createQueryBuilder();
    }

    public function createNativeQuery($sql, $rsm)
    {
        if (!$rsm) {
            $rsm = new \Doctrine\ORM\Query\ResultSetMapping();
        }
        return $this->em->createNativeQuery($sql, $rsm);
    }

    public function query($sql)
    {
        $rsm   = new \Doctrine\ORM\Query\ResultSetMapping();
        $query = $this->em->createNativeQuery($sql, $rsm);
        return $query->getResult();
    }

    /**
     *
     * @param string       $name        The class name of the entity to find.
     * @param mixed        $id          The identity of the entity to find.
     * @param integer|null $lockMode    One of the \Doctrine\DBAL\LockMode::* constants
     *                                  or NULL if no specific lock mode should be used
     *                                  during the search.
     * @param integer|null $lockVersion The version of the entity to find when using
     *                                  optimistic locking.
     *
     * @return object|null The entity instance or NULL if the entity can not be found.
     */
    public function entity($name, $id, $lockMode = null, $lockVersion = null)
    {
        $entityName = '\\' . PROJECT_NAMESPACE . '\\models\\entity\\' . mb_convert_case($name, MB_CASE_TITLE);
        return $this->em->find($entityName, $id, $lockMode, $lockVersion);
    }

    /**
     * save
     *
     * @param object $entity
     *
     * @return null
     */
    public function save($entity)
    {
        $this->em->persist($entity);
        $this->em->flush();
    }

}
