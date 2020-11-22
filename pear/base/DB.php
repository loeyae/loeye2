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
 * @version  2018-07-23 22:44:28
 * @link     https://github.com/loeyae/loeye2.git
 */

namespace loeye\base;

use Doctrine\Common\Annotations\AnnotationException;
use Doctrine\Common\Cache\ApcuCache;
use Doctrine\Common\Cache\ArrayCache;
use Doctrine\Common\Cache\MemcachedCache;
use Doctrine\Common\Cache\PhpFileCache;
use Doctrine\Common\Cache\RedisCache;
use Doctrine\Common\Persistence\ObjectRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NativeQuery;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\ORM\Query\ResultSetMapping;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\TransactionRequiredException;
use loeye\error\BusinessException;
use loeye\lib\Secure;
use loeye\std\CacheTrait;
use loeye\std\ConfigTrait;
use Psr\Cache\InvalidArgumentException;
use Symfony\Component\Cache\Adapter\ApcuAdapter;
use Symfony\Component\Cache\Exception\CacheException;
use Throwable;

/**
 * Description of DB
 *
 * @author   Zhang Yi <loeyae@gmail.com>
 */
class DB
{

    use ConfigTrait;
    use CacheTrait;

    public const BUNDLE = 'database';

    /**
     *
     * @var EntityManager
     */
    protected $em;
    protected $defaultType;
    protected $isDevMode = false;
    protected static $_instance;
    protected $encryptMode = ENCRYPT_MODE_EXPLICIT;

    /**
     * __construct
     *
     * @param AppConfig $appConfig AppConfig
     * @param string|null $type type
     * @throws Throwable
     * @throws InvalidArgumentException
     */
    public function __construct(AppConfig $appConfig, $type = null)
    {
        $property = $appConfig->getPropertyName();
        $settings = $appConfig->getSetting('application.database');
        $config = $this->databaseConfig($appConfig);
        $this->defaultType = $settings['default'] ?? null;
        $this->isDevMode = $settings['is_dev_mode'] ?? false;
        $this->encryptMode = $settings['encrypt_mode'] ?? ENCRYPT_MODE_EXPLICIT;
        $this->_getEntityManager($appConfig, $config, $property, $type);
    }

    /**
     *
     * @return boolean
     */
    public function getDevMode(): bool
    {
        return $this->isDevMode;
    }

    /**
     * getInstance
     *
     * @param AppConfig $appConfig AppConfig
     * @param string|null $type type
     * @param string|null $sign sign
     *
     * @return DB
     * @throws InvalidArgumentException
     * @throws Throwable
     */
    public static function getInstance(AppConfig $appConfig, $type = null, $sign = null): DB
    {
        $it = ($type ?? 'default');
        $it = md5($it . $sign);
        if (!isset(self::$_instance[$it])) {
            self::$_instance[$it] = new self($appConfig, $type);
        }
        return self::$_instance[$it];
    }

    /**
     * _getEntityManager
     *
     * @param AppConfig $appConfig AppConfig
     * @param Configuration $config Configuration
     * @param string $property property
     * @param string $type type
     *
     * @return void
     * @throws AnnotationException
     * @throws BusinessException
     * @throws CacheException
     * @throws InvalidArgumentException
     * @throws ORMException
     */
    private function _getEntityManager(AppConfig $appConfig, Configuration $config, $property, $type): void
    {
        $key = $type ?? $this->defaultType;
        if (!$key) {
            throw new BusinessException('Invalid database type', BusinessException::INVALID_CONFIG_SET_CODE);
        }
        $dbSetting = $config->get($key);
        if (!$dbSetting) {
            throw new BusinessException('Invalid db setting', BusinessException::INVALID_CONFIG_SET_CODE);
        }
        if (ENCRYPT_MODE_CRYPT === $this->encryptMode && $dbSetting['password']) {
            $dbSetting['password'] = Secure::crypt($property, $dbSetting['password'], true);
        } elseif (ENCRYPT_MODE_KEYDB === $this->encryptMode && $dbSetting['password']) {
            $dbSetting['password'] = Secure::getKeyDb($property, $dbSetting['password']);
        }
        $cache = $this->getCache($appConfig);
        $this->em = \loeye\database\EntityManager::getManager($dbSetting, $property, $cache);
        if (!isset($dbSetting['softAble']) || $dbSetting['softAble']) {
            $this->em->getFilters()->enable("soft-deleteable");
        }

    }

    /**
     * getCache
     *
     * @param AppConfig $appConfig AppConfig
     * @return \Doctrine\Common\Cache\Cache
     */
    protected function getCache(AppConfig $appConfig): \Doctrine\Common\Cache\Cache
    {
        if ($this->isDevMode) {
            return new ArrayCache();
        }
        if (ApcuAdapter::isSupported()) {
            return new ApcuCache();
        }
        $cacheType = $appConfig->getSetting('application.cache');
        if (Cache::CACHE_TYPE_REDIS === $cacheType) {
            $cache = new RedisCache();
            $config = $this->cacheConfig($appConfig);
            $setting = $config->get($cacheType);
            $redis = $this->getRedisClient($setting);
            $cache->setRedis($redis);
        } elseif (Cache::CACHE_TYPE_MEMCACHED === $cacheType) {
            $cache = new MemcachedCache();
            $config = $this->cacheConfig($appConfig);
            $setting = $config->get($cacheType);
            $memcached = $this->getMemcachedClient($setting);
            $cache->setMemcached($memcached);
        } else {
            $directory = RUNTIME_CACHE_DIR . D_S . self::BUNDLE;
            $cache = new PhpFileCache($directory);
        }
        return $cache;
    }

    /**
     * entityManager
     *
     * @return EntityManager
     */
    public function entityManager(): EntityManager
    {
        return $this->em;
    }

    /**
     * em
     *
     * @return EntityManager
     */
    public function em(): EntityManager
    {
        return $this->em;
    }

    /**
     * Create Query Builder
     *
     * @return QueryBuilder
     */
    public function createQueryBuilder(): QueryBuilder
    {
        return $this->em->createQueryBuilder();
    }

    /**
     * Create Query Builder
     *
     * @return QueryBuilder
     */
    public function qb(): QueryBuilder
    {
        return $this->em->createQueryBuilder();
    }

    /**
     *
     * @param string $sql sql
     * @param ResultSetMapping|null $rsm
     *
     * @return NativeQuery
     */
    public function createNativeQuery($sql, $rsm = null): NativeQuery
    {
        if (!$rsm) {
            $rsm = new ResultSetMapping();
        }
        return $this->em->createNativeQuery($sql, $rsm);
    }

    /**
     * query
     *
     * @param string $sql sql
     *
     * @return mixed
     */
    public function query($sql)
    {
        $query = $this->createNativeQuery($sql);
        return $query->getResult();
    }

    /**
     * Finds entities by a set of criteria.
     *
     * @param string $entityName The class name of the entity to find
     *
     * @return ObjectRepository|EntityRepository The repository class.
     */
    public function repository($entityName)
    {
        return $this->em->getRepository($entityName);
    }

    /**
     * Finds a single entity by a set of criteria.
     *
     * @param string $entityName The class name of the entity to find
     * @param array $criteria criteria
     * @param array|null $orderBy order by
     *
     * @return object|null The entity instance or NULL if the entity can not be found.
     */
    public function one($entityName, $criteria, $orderBy = null)
    {
        return $this->em->getRepository($entityName)->findOneBy($criteria, $orderBy);
    }

    /**
     *
     * @param string $name The class name of the entity to find.
     * @param mixed $id The identity of the entity to find.
     * @param integer|null $lockMode One of the \Doctrine\DBAL\LockMode::* constants
     *                                  or NULL if no specific lock mode should be used
     *                                  during the search.
     * @param integer|null $lockVersion The version of the entity to find when using
     *                                  optimistic locking.
     *
     * @return object|null The entity instance or NULL if the entity can not be found.
     * @throws ORMException
     * @throws OptimisticLockException
     * @throws TransactionRequiredException
     */
    public function entity($name, $id, $lockMode = null, $lockVersion = null)
    {
        return $this->em->find($name, $id, $lockMode, $lockVersion);
    }

    /**
     * save
     *
     * @param object $entity
     *
     * @return bool
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function save($entity): bool
    {
        $this->em->persist($entity);
        $this->em->flush();
        return true;
    }

    /**
     * flush
     *
     * @return bool
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function flush(): bool
    {
        $this->em->flush();
        return true;
    }

    /**
     * refresh
     *
     * @param object $entity
     *
     * @return object
     * @throws ORMException
     */
    public function refresh($entity)
    {
        $this->em->refresh($entity);
        return $entity;
    }

    /**
     * remove
     *
     * @param object $entity
     *
     * @return bool
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function remove($entity): bool
    {
        $this->em->remove($entity);
        $this->em->flush();
        return true;
    }

}
