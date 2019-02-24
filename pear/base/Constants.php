<?php

/**
 * Constants.php
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

namespace loeye\base;

define('LOEYE_DIR', dirname(dirname(__FILE__)));

if (!defined('LOEYE_PROCESS_MODE__NORMAL')) {
    define('LOEYE_PROCESS_MODE__NORMAL', 1);
}

if (!defined('LOEYE_PROCESS_MODE__TEST')) {
    define('LOEYE_PROCESS_MODE__TEST', 2);
}

if (!defined('LOEYE_MODE_DEV')) {
    define('LOEYE_MODE_DEV', 'dev');
}

if (!defined('LOEYE_MODE_TEST')) {
    define('LOEYE_MODE_TEST', 'test');
}

if (!defined('LOEYE_MODE_PROD')) {
    define('LOEYE_MODE_PROD', 'prod');
}

if (!defined('PROJECT_NAMESPACE')) {
    define('PROJECT_NAMESPACE', 'app');
}

if (!defined("PROJECT_DIR")) {
    define('PROJECT_DIR', realpath(LOEYE_DIR . '/../' . PROJECT_NAMESPACE));
}

if (!defined("PROJECT_CONFIG_DIR")) {
    define('PROJECT_CONFIG_DIR', PROJECT_DIR . '/conf');
}

if (!defined("PROJECT_KEYDB_DIR")) {
    define('PROJECT_KEYDB_DIR', PROJECT_DIR . '/keydb');
}

if (!defined("PROJECT_LOCALE_DIR")) {
    define('PROJECT_LOCALE_DIR', PROJECT_DIR . '/resource');
}

if (!defined("PROJECT_ERRORPAGE_DIR")) {
    define('PROJECT_ERRORPAGE_DIR', PROJECT_DIR . '/errors');
}

if (!defined("PROJECT_VIEWS_DIR")) {
    define('PROJECT_VIEWS_DIR', PROJECT_DIR . '/views');
}

if (!defined("PROJECT_MODELS_DIR")) {
    define('PROJECT_MODELS_DIR', PROJECT_DIR . '/models');
}

if (!defined("PROJECT_DATA_DIR")) {
    define('PROJECT_DATA_DIR', PROJECT_DIR . '/data');
}

if (!defined("RUNTIME_DIR")) {
    define('RUNTIME_DIR', realpath(LOEYE_DIR . '/../runtime'));
}

if (!defined("RUNTIME_CACHE_DIR")) {
    define('RUNTIME_CACHE_DIR', RUNTIME_DIR . '/cache');
}

if (!defined("RUNTIME_LOG_DIR")) {
    define('RUNTIME_LOG_DIR', RUNTIME_DIR . '/log');
}

if (!defined("RUNTIME_LOGGER_LEVEL")) {
    define('RUNTIME_LOGGER_LEVEL', Logger::LOEYE_LOGGER_TYPE_WARNING);
}

const PROJECT_SUCCESS = "";
const RENDER_TYPE_SEGMENT = 'segment';
const RENDER_TYPE_HTML = 'html';
const RENDER_TYPE_XML = 'xml';
const RENDER_TYPE_JSON = 'json';
