<?php

/**
 * Dispatcher.php
 *
 * @author  Zhang Yi <loeyae@gmail.com>
 * @version 2018-07-24 09:26:46
 */

mb_internal_encoding('UTF-8');

define('APP_BASE_DIR', dirname(__DIR__));

require_once APP_BASE_DIR . DIRECTORY_SEPARATOR .'vendor'. DIRECTORY_SEPARATOR .'autoload.php';

define('LOEYE_MODE', LOEYE_MODE_DEV);

$dispatcher = new loeye\web\Dispatcher();
$dispatcher->dispatche();
