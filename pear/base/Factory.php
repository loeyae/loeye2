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
 * @version  2018-07-23 22:44:28
 * @link     https://github.com/loeyae/loeye2.git
 */

namespace loeye\base;

use \loeye\error\BusinessException;

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
            throw new BusinessException(BusinessException::INVALID_PLUGIN_SET_MSG, BusinessException::INVALID_PLUGIN_SET_CODE);
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
            throw new BusinessException(BusinessException::INVALID_RENDER_SET_MSG, BusinessException::INVALID_RENDER_SET_CODE);
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
            throw new BusinessException(BusinessException::INVALID_RENDER_SET_MSG, BusinessException::INVALID_RENDER_SET_CODE);
        }
        $file = AutoLoadRegister::realAliasFile($setting['src']);
        if (!is_file($file)) {
            $dno  = strrpos($file, ".");
            $file = PROJECT_VIEWS_DIR . '/'
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
                width: 100%;
                height: auto;
                margin-top: 100px;
                margin-left: auto;
                margin-right: auto;
            }
            #main div {
                width: 90%;
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
                margin: 10px auto;
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
            <div><span>error trace info:</span>{$e->getTraceAsString()}</div>
        </div>
    </body>
</html>
EOF;
        echo $html;
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
