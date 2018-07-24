<?php

/**
 * bootstrap.php
 *
 * @author  Zhang Yi <loeyae@gmail.com>
 * @version SVN: $Id: Zhang Yi $
 */
define('RUNTIME_NS', 'app');
define('RUNTIME_PP', 'tools');
define('RUNTIME_TYPE', 'default');

use Doctrine\ORM\Tools\Setup;
use Doctrine\ORM\EntityManager;
use Doctrine\Common\Cache\FilesystemCache;

require_once dirname(__FILE__) .DIRECTORY_SEPARATOR. '/pear/base' .DIRECTORY_SEPARATOR. 'Constants.php';

function GetEntityManager() {
    $config = new \loeye\base\Configuration(RUNTIME_PP, 'database');
    $key = RUNTIME_TYPE;
    if (!$key) {
        throw new Exception('Invalid database type.');
    }
    $dbSetting = $config->get($key);
    if (!$dbSetting) {
        throw new Exception('Invalid database setting.');
    }
    $entitiesDir = PROJECT_MODELS_DIR;
    $ormCacheDir = RUNTIME_CACHE_DIR .'/'. RUNTIME_NS .'/'. RUNTIME_PP .'/db';
    $cacheDir = $ormCacheDir .'/cache';
    $dbconfig = Setup::createAnnotationMetadataConfiguration(array($entitiesDir), true);
//    $dbconfig->setEntityNamespaces(array(RUNTIME_NS .'\\models'));
    $cache = new FilesystemCache($cacheDir, 'cache');
    $dbconfig->setMetadataCacheImpl($cache);
    //手动创建entities时使用
//    $driverImpl = $dbconfig->newDefaultAnnotationDriver(array($entitiesDir));
    #从数据库反向工程生产entities时使用
    $ymlDir = $ormCacheDir .'/yml';
    $driverImpl = new \Doctrine\ORM\Mapping\Driver\YamlDriver(array($ymlDir));

    $dbconfig->setMetadataDriverImpl($driverImpl);
    $dbconfig->setQueryCacheImpl($cache);

//    $proxiesDir = $ormCacheDir.'/proxies';
//    $dbconfig->setProxyDir($proxiesDir);
//    $dbconfig->setProxyNamespace('Proxies');

    $logger = new \Doctrine\DBAL\Logging\LoggerChain();
    $logger->addLogger(new loeye\database\Logger());
    $dbconfig->setSQLLogger($logger);
//    $dbconfig->setAutoGenerateProxyClasses(true);
    return EntityManager::create($dbSetting, $dbconfig);
}
