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
        $config   = $this->propertyConfig($property, null);
        if (is_string($settins)) {
            $this->defaultType = $settins;
        } else {
            $this->defaultType = $settins['default'] ?? null;
            $this->isDevMode   = $settins['is_dev_mode'] ?? false;
        }
        $this->_getEntityManager($config, $property, $type);
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
    private function _getEntityManager(Configuration $config, $property, $type)
    {
        $key = $type ?? $this->defaultType;
        if (!$key) {
            throw new Exception('Invalid database type.');
        }
        $dbSetting = $config->get($key);
        if (!$dbSetting) {
            throw new Exception('Invalid database setting.');
        }
        $entitiesDir = PROJECT_MODELS_DIR;
        $ormCacheDir = RUNTIME_CACHE_DIR . '/' . PROJECT_NAMESPACE . '/' . $property . '/db';
        $ymlDir      = $ormCacheDir . '/yml';
        $cacheDir    = $ormCacheDir . '/cache';
        $dbconfig    = Setup::createAnnotationMetadataConfiguration(array($entitiesDir), $this->isDevMode);
//        $dbconfig->setEntityNamespaces(array(PROJECT_NAMESPACE .'\\models'));
        $cache       = new FilesystemCache($cacheDir, 'cache');
        $dbconfig->setMetadataCacheImpl($cache);
//        $driverImpl = $dbconfig->newDefaultAnnotationDriver(array($entitiesDir));
        $driverImpl  = new \Doctrine\ORM\Mapping\Driver\YamlDriver(array($ymlDir));
        $dbconfig->setMetadataDriverImpl($driverImpl);
        $dbconfig->setQueryCacheImpl($cache);

//        $proxiesDir = $ormCacheDir.'/proxies';
//        $dbconfig->setProxyDir($proxiesDir);
//        $dbconfig->setProxyNamespace('Proxies');

        $logger   = new \Doctrine\DBAL\Logging\LoggerChain();
        $logger->addLogger(new \loeye\database\Logger());
        $dbconfig->setSQLLogger($logger);
//        $dbconfig->setAutoGenerateProxyClasses(true);
        $this->em = EntityManager::create($dbSetting, $dbconfig);
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

    public function entity($name, $id)
    {
        $entityName = '\\' . PROJECT_NAMESPACE . '\\models\\' . $name;
        return $this->em->find($entityName, $id);
    }

    public function save($entity)
    {
        $this->em->persist($entity);
        $this->em->flush();
    }

}
