<?php

/**
 * service.php
 *
 * @author  Zhang Yi <loeyae@gmail.com>
 * @version 2019-02-25 14:50:29
 */

mb_internal_encoding('UTF-8');

define('APP_BASE_DIR', dirname(__DIR__));

require_once APP_BASE_DIR . DIRECTORY_SEPARATOR .'vendor'. DIRECTORY_SEPARATOR .'autoload.php';

define('LOEYE_MODE', LOEYE_MODE_DEV);

$dispatcher = new loeye\service\Dispatcher();
$dispatcher->init($setting = ["rewrite"=> [
        '/<module:\w+>/<service:\w+>/<handler:\w+>' => '{module}/{service}/{handler}',
        '/<module:\w+>/<service:\w+>/<handler:\w+>/.+' => '{module}/{service}/{handler}',
    ]]);
$dispatcher->dispatche();
