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
 * @version  2018-07-23 22:44:28
 * @link     https://github.com/loeyae/loeye2.git
 */

namespace loeye\base;

define('D_S', DIRECTORY_SEPARATOR);
define('LOEYE_DIR', dirname(__FILE__, 2));

if (!defined('LOEYE_MODE_DEV')) {
    define('LOEYE_MODE_DEV', 'dev');
}

if (!defined('LOEYE_MODE_TEST')) {
    define('LOEYE_MODE_TEST', 'test');
}

if (!defined('LOEYE_MODE_PROD')) {
    define('LOEYE_MODE_PROD', 'prod');
}

if (!defined('LOEYE_MODE_UNIT')) {
    define('LOEYE_MODE_UNIT', 'unit');
}

if (!defined('PROJECT_NAMESPACE')) {
    define('PROJECT_NAMESPACE', 'app');
}

if (!defined('PROJECT_DIR')) {
    $projectDir = realpath(dirname(LOEYE_DIR) . D_S . PROJECT_NAMESPACE) ? dirname(LOEYE_DIR) .
        D_S . PROJECT_NAMESPACE : realpath(dirname(LOEYE_DIR, 4) . D_S . PROJECT_NAMESPACE);
    if (false === $projectDir) {
        $projectDir = realpath(dirname(LOEYE_DIR) . D_S . 'tests');
        define('RUNTIME_DIR', $projectDir . D_S . 'runtime');
        define('LOEYE_MODE', LOEYE_MODE_UNIT);
    }
    define('PROJECT_DIR', $projectDir);
}

if (!defined('PROJECT_CONFIG_DIR')) {
    define('PROJECT_CONFIG_DIR', PROJECT_DIR . D_S . 'conf');
}

if (!defined('PROJECT_KEYDB_DIR')) {
    define('PROJECT_KEYDB_DIR', PROJECT_DIR . D_S . 'keydb');
}

if (!defined('PROJECT_LOCALE_DIR')) {
    define('PROJECT_LOCALE_DIR', PROJECT_DIR . D_S . 'resource');
}

if (!defined('PROJECT_ERRORPAGE_DIR')) {
    define('PROJECT_ERRORPAGE_DIR', PROJECT_DIR . D_S . 'errors');
}

if (!defined('PROJECT_VIEWS_DIR')) {
    define('PROJECT_VIEWS_DIR', PROJECT_DIR . D_S . 'views');
}

if (!defined('PROJECT_HANDLE_DIR')) {
    define('PROJECT_HANDLE_DIR', PROJECT_DIR . D_S . 'handles');
}

if (!defined('PROJECT_MODELS_DIR')) {
    define('PROJECT_MODELS_DIR', PROJECT_DIR . D_S . 'models');
}

if (!defined('PROJECT_DATA_DIR')) {
    define('PROJECT_DATA_DIR', PROJECT_DIR . D_S . 'data');
}

if (!defined('RUNTIME_DIR')) {
    define('RUNTIME_DIR', dirname(PROJECT_DIR) . D_S . 'runtime');
}

if (!defined('RUNTIME_CACHE_DIR')) {
    define('RUNTIME_CACHE_DIR', RUNTIME_DIR . D_S . 'cache');
}

if (!defined('RUNTIME_LOG_DIR')) {
    define('RUNTIME_LOG_DIR', RUNTIME_DIR . D_S . 'log');
}

const PROJECT_SUCCESS = '';
const RENDER_TYPE_SEGMENT = 'segment';
const RENDER_TYPE_HTML = 'html';
const RENDER_TYPE_XML = 'xml';
const RENDER_TYPE_JSON = 'json';
const ENCRYPT_MODE_EXPLICIT = 'explicit';
const ENCRYPT_MODE_CRYPT = 'crypt';
const ENCRYPT_MODE_KEYDB = 'keydb';
