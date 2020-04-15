<?php

/**
 * Dispatcher.php
 *
 */
 use loeye\web\Dispatcher;

mb_internal_encoding('UTF-8');

define('APP_BASE_DIR', dirname(__DIR__));
define('PROJECT_NAMESPACE', 'app');
define('PROJECT_DIR', realpath(APP_BASE_DIR . '/' . PROJECT_NAMESPACE));

require_once APP_BASE_DIR . DIRECTORY_SEPARATOR .'vendor'. DIRECTORY_SEPARATOR .'autoload.php';

define('LOEYE_MODE', LOEYE_MODE_DEV);

$dispatcher = new Dispatcher();
$dispatcher->dispatch();
