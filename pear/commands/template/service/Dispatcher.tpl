<?php

/**
 * Service.php
 *
 * @author Zhang Yi <loeyae@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache License
 * @version <{$smarty.now|date_format: "%Y-%m-%d %H:%M:%S"}>
 */
 use loeye\service\Dispatcher;

mb_internal_encoding('UTF-8');

define('APP_BASE_DIR', dirname(__DIR__));
define('PROJECT_NAMESPACE', 'app');
define('PROJECT_DIR', realpath(APP_BASE_DIR . '/' . PROJECT_NAMESPACE));

require_once APP_BASE_DIR . DIRECTORY_SEPARATOR .'vendor'. DIRECTORY_SEPARATOR .'autoload.php';

define('LOEYE_MODE', LOEYE_MODE_DEV);

$dispatcher = new Dispatcher();
$dispatcher->init([
    'rewrite' => [
        '/<module:\w+>/<service:\w+>/<handler:\w+>/<id:\w+>' => '{module}/{service}/{handler}',
        '/<module:\w+>/<service:\w+>/<handler:\w+>' => '{module}/{service}/{handler}',
    ]
]);
$dispatcher->dispatch();
