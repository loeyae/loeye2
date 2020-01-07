<?php

/**
 * cli-config.php
 *
 * @author  Zhang Yi <loeyae@gmail.com>
 * @version SVN: $Id: Zhang Yi $
 */
use Doctrine\ORM\Tools\Console\ConsoleRunner;

define('LOEYE_MODE', 'prod');

$property        = $_SERVER['argv'][1];
unset($_SERVER['argv'][1]);
$_SERVER['argv'] = array_values($_SERVER['argv']);
$appConfig       = new \loeye\base\AppConfig($property);
$dbKey           = $appConfig->getSetting('application.database') ?? 'default';
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
