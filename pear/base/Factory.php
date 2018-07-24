<?php

/**
 * Factory.php
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

/**
 * Description of Factory
 *
 * @author   Zhang Yi <loeyae@gmail.com>
 */
class Factory
{

    /**
     * getPlugin
     *
     * @param array $pluginSetting plugin setting
     *
     * @return \LOEYE\class
     * @throws Exception
     */
    public static function getPlugin($pluginSetting)
    {
        if (!isset($pluginSetting['name'])) {
            throw new Exception(
                    '无效的plugin设置', Exception::INVALID_CONFIG_SET_CODE);
        }
        $class = $pluginSetting['name'];
        if (!isset($pluginSetting['src'])) {
            $rec = new \ReflectionClass($class);
            return $rec->newInstanceArgs();
        }
        $file = AutoLoadRegister::realAliasFile($pluginSetting['src']);
        AutoLoadRegister::loadFile($file);
        $rec  = new \ReflectionClass($class);
        return $rec->newInstanceArgs();
    }

    /**
     * includeLayout
     *
     * @param \LOEYE\Context $context context
     * @param string              $content content
     * @param array               $setting view setting
     *
     * @return void
     * @throws Exception
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public static function includeLayout(Context $context, $content, $setting)
    {
        if (!isset($setting['layout'])) {
            throw new Exception(
                    '无效的render设置', Exception::INVALID_CONFIG_SET_CODE);
        }
        $file = AutoLoadRegister::realAliasFile($setting['layout']);
        if (!is_file($file)) {
            $dno  = strrpos($file, ".");
            $file = PROJECT_VIEWS_BASE_DIR . '/'
                    . strtr(substr($file, 0, $dno), ".", "/") . substr($file, $dno);
        }
        include $file;
    }

    /**
     * includeView
     *
     * @param \LOEYE\Context $context context
     * @param array               $setting view setting
     *
     * @return void
     * @throws Exception
     */
    public static function includeView(Context $context, $setting)
    {
        if (!isset($setting['src'])) {
            throw new Exception(
                    '无效的render设置', Exception::INVALID_CONFIG_SET_CODE);
        }
        $file = AutoLoadRegister::realAliasFile($setting['src']);
        if (!is_file($file)) {
            $dno  = strrpos($file, ".");
            $file = PROJECT_VIEWS_BASE_DIR . '/'
                    . strtr(substr($file, 0, $dno), ".", "/") . substr($file, $dno);
        }
        self::includeHandle($context, $setting);
        include $file;
    }

    /**
     * includeHandle
     *
     * @param \LOEYE\Context $context context
     * @param array               $setting setting
     *
     * @return void
     * @throws Exception
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    static public function includeHandle(Context $context, $setting)
    {
        if (isset($setting['handle'])) {
            $handle = AutoLoadRegister::realAliasFile($setting['handle']);
            if (!is_file($handle)) {
                $dno    = strrpos($handle, ".");
                $handle = PROJECT_HANDLES_BASE_DIR . '/'
                        . strtr(substr($handle, 0, $dno), ".", "/") . substr($handle, $dno);
            }
            include $handle;
        }
    }

    /**
     * getRender
     *
     * @param string $format format
     *
     * @return \LOEYE\Render
     * @throws Exception
     */
    static public function getRender($format = 'segment')
    {
        $renderFormat = array(
            RENDER_TYPE_HTML,
            RENDER_TYPE_SEGMENT,
            RENDER_TYPE_JSON,
            RENDER_TYPE_XML,
        );
        if (!in_array($format, $renderFormat)) {
            $format = 'segment';
        }
        $class     = '' . ucfirst($format) . 'Render';
        $className = '\\loeye\\render\\' . $class;
        $renderObj = new \ReflectionClass($className);
        return $renderObj->newInstanceArgs();
    }

    /**
     * includeErrorPage
     *
     * @param \LOEYE\Context $context   context
     * @param \Exception          $e         exception
     * @param string              $errorPage error page
     *
     * @return void
     */
    static public function includeErrorPage(
            Context $context, \Exception $e, $errorPage = null
    )
    {
        $defaultError = "General";
        $property     = null;
        if ($context->getAppConfig() instanceof AppConfig) {
            $property = $context->getAppConfig()->getPropertyName();
        }
        $errorPath        = PROJECT_ERRORPAGE_DIR . '/';
        $defaultErrorPage = $errorPath . $defaultError . 'Error.php';
        if (!empty($property)) {
            $propertyErrorPath = PROJECT_ERRORPAGE_DIR . '/' . $property . '/';
            $propertyErrorPage = $propertyErrorPath . $defaultError . 'Error.php';
        }
        if (empty($errorPage)) {
            if (isset($propertyErrorPath)) {
                $errorPage = $propertyErrorPath . 'Error' . substr($e->getCode(), 0, 3) . '.php';
            }
            if (!is_file($errorPage)) {
                $errorPage = $errorPath . 'Error' . substr($e->getCode(), 0, 3) . '.php';
            }
        }
        if (is_file($errorPage)) {
            include $errorPage;
        } else if (isset($propertyErrorPage) && is_file($propertyErrorPage)) {
            include $propertyErrorPage;
        } else if (is_file($defaultErrorPage)) {
            include $defaultErrorPage;
        } else {
            self::_getErrorPageInfo($e);
        }
    }

    /**
     * _getErrorPageInfo
     *
     * @param \Exception $e e
     *
     * @return void
     */
    static private function _getErrorPageInfo($e)
    {
        $html = <<<EOF
<!DOCTYPE html>
<html>
    <head>
        <title>出错了</title>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <style>
            body {
                width: 100%;
                height: auto;
                margin: 0px auto;
            }
            #main {
                width: 400px;
                height: auto;
                margin-top: 100px;
                margin-left: auto;
                margin-right: auto;
            }
            #main div {
                width: 400px;
                height: auto;
                line-height: 30px;
                text-align:center;
                overflow: visible;
                white-space: normal;
                clear:both;
                border: 1px #0f3c54 solid;
                font-size: 1.1em;
                color: #ff0000;
                padding: 10px;
                margin-bottom: 10px;
            }
            #main div span {
                margin-right: 10px;
            }
        </style>
    </head>
    <body>
        <div id="main">
            <div><span>error code: </span>{$e->getCode()}</div>
            <div><span>error message: </span>{$e->getMessage()}</div>
        </div>
    </body>
</html>
EOF;
        echo $html;
    }

    /**
     * getDao
     *
     * @param string $name          dao name ex: cms.contentDao cms => dir  contentDao => file name
     * @param string $configBaseDir config base dir
     * @param string $bundle        bundle
     * @param string $dbr           database name for read
     * @param string $dbw           database name for write
     *
     * @return \LOEYE\DAO
     * @throws Exception
     *
     * @staticvar array $pdos pdo list
     * @staticvar array $daos dao list
     *
     */
    public static function getDao($name, $configBaseDir, $bundle, $dbr, $dbw)
    {
        static $daos    = array();
        $arr            = explode('.', $name);
        $baseName       = array_pop($arr);
        $modelNamespace = PROJECT_BASE_NAMESPACE . '\\MODELS';
        if ($arr) {
            $namespace = mb_strtoupper(implode('\\', $arr));
            if ($namespace == __NAMESPACE__) {
                $className = '\\' . $namespace . '\\' . $baseName;
            } else {
                $className = $modelNamespace . '\\' . $namespace . '\\' . $baseName;
            }
        } else {
            $className = $modelNamespace . '\\' . $baseName;
        }
        if (isset($daos[$className]) && ($daos[$className] instanceof DAO)) {
            return $daos[$className];
        }
        $pdos             = self::getPDO($bundle, $dbr, $dbw, $configBaseDir);
        $pdor             = $pdos['r'];
        $pdow             = $pdos['w'];
        $ref              = new \ReflectionClass($className);
        $dao              = $ref->newInstance($pdor, $pdow);
        $daos[$className] = $dao;
        return $dao;
    }

    /**
     * model
     *
     * @param string                $name      name
     * @param \LOEYE\AppConfig $appConfig app config
     * @param string                $id        id
     *
     * @return \LOEYE\DAO
     * @throws Exception
     */
    static public function model($name, AppConfig $appConfig, $id = 'db')
    {
        static $daos = array();
        if (isset($daos[$name]) && ($daos[$name] instanceof DAO)) {
            return $daos[$name];
        }
        $propertyName   = $appConfig->getPropertyName();
        $bundle         = $propertyName . '/db';
        $db             = $appConfig->getSetting($id);
        $dbr            = Utils::checkKeyExist($db, 'read');
        $dbw            = Utils::checkKeyExist($db, 'write');
        $modelNamespace = $appConfig->getSetting('model_namespace', '');
        if (!$modelNamespace) {
            $modelNamespace = PROJECT_BASE_NAMESPACE . '\\MODELS\\' . mb_convert_case($propertyName, MB_CASE_UPPER);
        }
        $daoName     = $modelNamespace . '\\' . $name;
        $pdos        = self::getPDO($bundle, $dbr, $dbw);
        $pdor        = $pdos['r'];
        $pdow        = $pdos['w'];
        $ref         = new \ReflectionClass($daoName);
        $dao         = $ref->newInstance($pdor, $pdow);
        $daos[$name] = $dao;
        return $dao;
    }

    /**
     * db
     *
     * @param \LOEYE\AppConfig $appConfig context
     * @param string                $id        db setting id
     *
     * @return \LOEYE\DAO
     *
     * @staticvar array $instance dao instance
     */
    static public function db(AppConfig $appConfig, $id = 'db')
    {
        static $instance = array();
        $propertyName    = $appConfig->getPropertyName();
        $bundle          = $propertyName . '/db';
        $db              = $appConfig->getSetting($id);
        $dbr             = Utils::checkKeyExist($db, 'read');
        $dbw             = Utils::checkKeyExist($db, 'write');
        $dbKey           = md5(print_r($db, true));
        if (isset($instance[$dbKey]) && $instance[$dbKey] instanceof DAO) {
            return $instance[$dbKey];
        } else {
            $pdos             = self::getPDO($bundle, $dbr, $dbw);
            $pdor             = $pdos['r'];
            $pdow             = $pdos['w'];
            $dao              = new DAO($pdor, $pdow);
            $instance[$dbKey] = $dao;
            return $dao;
        }
    }

    /**
     * getPDO
     *
     * @param string $bundle        bundle
     * @param string $dbr           dbr setting name
     * @param string $dbw           dbw setting name
     * @param string $configBaseDir config base dir
     *
     * @staticvar array  $pdos           pdo list
     *
     * @return array
     */
    static public function getPDO($bundle, $dbr, $dbw, $configBaseDir = null)
    {
        static $pdos = array();
        if (!$configBaseDir) {
            $configBaseDir = PROJECT_CONFIG_DIR;
        }
        $rkey = md5($configBaseDir . $bundle . $dbr);
        $wkey = md5($configBaseDir . $bundle . $dbw);
        if (isset($pdos[$rkey]) && $pdos[$rkey] instanceof \PDO && isset($pdos[$wkey]) && $pdos[$wkey] instanceof \PDO) {
            $pdor = $pdos[$rkey];
            $pdow = $pdos[$wkey];
        } else {
            $pdo         = new PDO($configBaseDir, $bundle);
            $pdor        = $pdo->connect($dbr);
            $pdow        = $pdo->connect($dbw);
            $pdos[$rkey] = $pdor;
            $pdos[$wkey] = $pdow;
        }
        return ['r' => $pdor, 'w' => $pdow];
    }

    /**
     * autoload
     *
     * @param string $dir    dir
     * @param bool   $ignore ignore children dir
     *
     * @return void
     */
    static public function autoload($dir, $ignore = true)
    {
        $dir = AutoLoadRegister::realAliasFile($dir);
        if (!file_exists($dir)) {
            return;
        }
        AutoLoadRegister::addDir($dir);
        if (!$ignore) {
            foreach (new \FilesystemIterator($dir) as $fs) {
                if ($fs->isDir()) {
                    static::autoload($fs->getRealPath());
                }
            }
        }
    }

    /**
     * cache
     *
     * @param \loeye\base\AppConfig $appConfig context
     * @param string                $type      type
     *
     * @return SimpleFileCache|\Memcache|\Memcached|\Redis
     */
    static public function cache(AppConfig $appConfig, $type = null)
    {
        $cache = Cache::init($appConfig, $type);
        if (!$cache) {
            $cache = Cache::init($appConfig, Cache::CACHE_TYPE_FILE);
        }
        return $cache;
    }

}
