<?php

/**
 * EntityManager.php
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

namespace loeye\database;

use Doctrine\ORM\Tools\Setup;
use Doctrine\ORM\EntityManager as EM;
use Doctrine\Common\Cache\FilesystemCache;
use Doctrine\ORM\Mapping\Driver\YamlDriver;

/**
 * EntityManager
 *
 * @author   Zhang Yi <loeyae@gmail.com>
 */
class EntityManager
{

    static protected $entitiesDir = PROJECT_MODELS_DIR . '/entity';
    static protected $schemeDir   = PROJECT_MODELS_DIR . '/scheme';
    static protected $proxiesDir  = PROJECT_MODELS_DIR . '/proxy';
    static protected $cacheDir    = RUNTIME_CACHE_DIR . '/' . PROJECT_NAMESPACE . '/db';
    static protected $isDevMode   = LOEYE_MODE == LOEYE_MODE_DEV ? true : false;

    static public function getManager($dbSetting, $fromDB = false)
    {
        $dbconfig = Setup::createAnnotationMetadataConfiguration([], static::$isDevMode);
//        $dbconfig->add(array(PROJECT_NAMESPACE .'\\models'));
//        $dbconfig->setEntityNamespaces(['\\app\\models\\entity']);
        $cache    = new FilesystemCache(static::$cacheDir, 'cache');
        $dbconfig->setMetadataCacheImpl($cache);
        if (!$fromDB) {
            $driverImpl = $dbconfig->newDefaultAnnotationDriver(array(static::$entitiesDir));
        } else {
            $driverImpl = new YamlDriver(array(static::$schemeDir));
        }
//        $driverImpl->setGlobalBasename($file);
        $dbconfig->setMetadataDriverImpl($driverImpl);
        $dbconfig->setQueryCacheImpl($cache);

        $dbconfig->setProxyDir(static::$proxiesDir);
        $dbconfig->setProxyNamespace('Proxies');

        $logger = new \Doctrine\DBAL\Logging\LoggerChain();
        $logger->addLogger(new \loeye\database\Logger());
        $dbconfig->setSQLLogger($logger);
        $dbconfig->setAutoGenerateProxyClasses(true);
        return EM::create($dbSetting, $dbconfig);
    }

}
