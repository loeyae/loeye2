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
    echo ' ' . PHP_EOL;
    echo '             Not enough arguments (missing: "property, db-id").' . PHP_EOL;
    echo ' ' . PHP_EOL;
    echo 'loeye-orm <property> <db-id> [command] [--]' . PHP_EOL;
    exit(0);
}
$property        = $_SERVER['argv'][1];
$dbId            = $_SERVER['argv'][2];
unset($_SERVER['argv'][1]);
unset($_SERVER['argv'][2]);
$_SERVER['argv'] = array_values($_SERVER['argv']);
$command         = isset($_SERVER['argv'][1]) ? $_SERVER['argv'][1] : null;
if ($command == 'convert:mapping') {
    $_SERVER['argv'][1] = 'orm:convert-mapping';
    array_push($_SERVER['argv'], '--from-database');
    array_push($_SERVER['argv'], '-f');
    array_push($_SERVER['argv'], '--namespace=app\\models\\entity\\' . $property . '\\');
    array_push($_SERVER['argv'], 'annotation');
    array_push($_SERVER['argv'], realpath(PROJECT_DIR . '/../'));
} else if ($command == 'generate:proxies') {
    $_SERVER['argv'][1] = 'orm:generate-proxies';
    array_push($_SERVER['argv'], realpath(PROJECT_DIR . '/models/proxy'));
} else if ($command == 'generate:repositories') {
    $_SERVER['argv'][1] = 'orm:generate-repositories';
    array_push($_SERVER['argv'], realpath(PROJECT_DIR . '/../'));
} else if ($command == 'generate:entities') {
    $_SERVER['argv'][1] = 'orm:generate-entities';
    array_push($_SERVER['argv'], '--generate-annotations=true');
    array_push($_SERVER['argv'], '--regenerate-entities=true');
    array_push($_SERVER['argv'], '--update-entities=true');
    array_push($_SERVER['argv'], '--generate-methods=true');
    array_push($_SERVER['argv'], '--no-backup');
    array_push($_SERVER['argv'], realpath(PROJECT_DIR . '/../'));
}
$appConfig     = new \loeye\base\AppConfig($property);
$dbKey         = $appConfig->getSetting('application.database.' . $dbId) ?? 'default';
$db            = \loeye\base\DB::getInstance($appConfig, $dbKey);
$entityManager = $db->em();
$platform      = $entityManager->getConnection()->getDatabasePlatform();
$platform->registerDoctrineTypeMapping("enum", "string");
$platform->registerDoctrineTypeMapping("set", "string");
$platform->registerDoctrineTypeMapping("varbinary", "string");
$platform->registerDoctrineTypeMapping("tinyblob", "text");
return ConsoleRunner::createHelperSet($entityManager);
