<?php

/**
 * cli-config.php
 *
 * @author  Zhang Yi <loeyae@gmail.com>
 * @version SVN: $Id: Zhang Yi $
 */
use Doctrine\ORM\Tools\Console\ConsoleRunner;

if (!defined('LOEYE_MODE')) {
    define('LOEYE_MODE', 'dev');
}

if (count($_SERVER['argv']) < 3) {
    echo ' '.PHP_EOL;
    echo '             Not enough arguments (missing: "property, db-id").'.PHP_EOL;
    echo ' '.PHP_EOL;
    echo 'loeye-orm <property> <db-id> [command] [--]'.PHP_EOL;
    exit(0);
}
$property        = $_SERVER['argv'][1];
$dbId = $_SERVER['argv'][2];
unset($_SERVER['argv'][1]);
unset($_SERVER['argv'][2]);
$_SERVER['argv'] = array_values($_SERVER['argv']);
$appConfig       = new \loeye\base\AppConfig($property);
$dbKey           = $appConfig->getSetting('application.database.'.$dbId) ?? 'default';
$config          = new \loeye\base\Configuration($property, 'database');
$dbSetting       = $config->get($dbKey);
if (!$dbSetting) {
    throw new Exception('Invalid database setting: ' . $dbKey . '.');
}
$entityManager = \loeye\database\EntityManager::getManager($dbSetting, true);
$platform      = $entityManager->getConnection()->getDatabasePlatform();
$platform->registerDoctrineTypeMapping("enum", "string");
$platform->registerDoctrineTypeMapping("set", "string");
$platform->registerDoctrineTypeMapping("varbinary", "string");
$platform->registerDoctrineTypeMapping("tinyblob", "text");
return ConsoleRunner::createHelperSet($entityManager);
