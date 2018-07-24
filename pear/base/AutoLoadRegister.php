<?php

/**
 * AutoLoadRegister.php
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
 * Description of AutoLoadRegister
 *
 * @author   Zhang Yi <loeyae@gmail.com>
 */
class AutoLoadRegister
{

    static protected $namespaceMap = [];
    static protected $dirMap = [];
    static protected $fileMap = [];
    static protected $aliasMap = [];
    static protected $singleMap = [];

    /**
     * addNamespace
     *
     * @param string $ns      namespace
     * @param string $path    dir path
     * @param bool   $prepend prepend
     *
     * @return boolean
     */
    static public function addNamespace($ns, $path, $prepend = false)
    {
        $path = static::realAliasFile($path);
        $maps = isset(static::$namespaceMap[$ns]) ? static::$namespaceMap[$ns] : [];
        if (in_array($path, $maps) || !is_dir($path)) {
            return false;
        }
        if ($prepend) {
            array_unshift($maps, $path);
        } else {
            array_push($maps, $path);
        }
        static::$namespaceMap[$ns] = $maps;
        return true;
    }

    /**
     * addFile
     *
     * @param string $file    file path
     * @param bool   $prepend prepend
     *
     * @return boolean
     */
    static public function addFile($file, $prepend = false)
    {
        $file = static::realAliasFile($file);
        if (in_array($file, static::$fileMap) || !file_exists($file)) {
            return false;
        }
        if ($prepend) {
            array_unshift(static::$fileMap, $file);
        } else {
            array_push(static::$fileMap, $file);
        }
        return true;
    }

    /**
     * addFile
     *
     * @param string $dir     dir path
     * @param bool   $prepend prepend
     *
     * @return boolean
     */
    static public function addDir($dir, $prepend = false)
    {
        $dir = static::realAliasFile($dir);
        if (in_array($dir, static::$dirMap) || !is_dir($dir)) {
            return false;
        }
        if ($prepend) {
            array_unshift(static::$dirMap, $dir);
        } else {
            array_push(static::$dirMap, $dir);
        }
        return true;
    }

    /**
     * addAlias
     *
     * @param string $alias   alias name
     * @param string $path    dir path
     * @param bool   $prepend prepend
     *
     * @return boolean
     */
    static public function addAlias($alias, $path, $prepend = false)
    {
        $path = static::realAliasFile($path);
        $maps = isset(static::$aliasMap[$alias]) ? static::$aliasMap[$alias] : [];
        if (in_array($path, $maps) || !is_dir($path)) {
            return false;
        }
        if ($prepend) {
            array_unshift($maps, $path);
        } else {
            array_push($maps, $path);
        }
        static::$aliasMap[$alias] = $maps;
        return true;
    }

    /**
     * addSingle
     *
     * @param string $className class name
     * @param string $file      file path
     *
     * @return boolean
     */
    static public function addSingle($className, $file)
    {
        $file      = static::realAliasFile($file);
        $className = trim($className, '\\');
        if (in_array($className, static::$singleMap) || !file_exists($file)) {
            return false;
        }
        static::$singleMap[$className] = $file;
        return true;
    }

    /**
     * loadAlias
     *
     * @param string $file alias path
     *
     * @return boolean
     */
    static public function loadAlias($file)
    {
        $realFile = static::realAliasFile($file);
        if ($realFile) {
            return static::loadFile($file);
        }
        return false;
    }

    /**
     * realAliasFile
     *
     * @param string $file alias path
     *
     * @return boolean
     */
    static public function realAliasFile($file)
    {
        $file = strtr($file, '\\', '/');
        if (mb_strpos($file, '@') !== 0) {
            return $file;
        }
        $s     = mb_strpos($file, '/');
        $alias = mb_substr($file, 1, $s - 1);
        if (isset(static::$aliasMap[$alias])) {
            $relativeFile = mb_substr($file, $s);
            foreach (static::$aliasMap[$alias] as $dir) {
                $realPath = realpath($dir . DIRECTORY_SEPARATOR . $relativeFile);
                if ($realPath) {
                    return $realPath;
                }
            }
        }
        return false;
    }

    /**
     * autoLoad
     *
     * @return void
     */
    static public function autoLoad()
    {
        foreach (static::$fileMap as $file) {
            static::loadFile($file);
        }
    }

    /**
     * initApp
     * @return void
     */
    static public function initApp()
    {
        if (!defined('PROJECT_DIR')) {
            $debugTrace = debug_backtrace();
            if (isset($debugTrace[1])) {
                $appBaseDir = realpath(dirname($debugTrace[1]['file']), PROJECT_NAMESPACE);
                define('PROJECT_DIR', $appBaseDir);
            }
        }
        if (defined('PROJECT_DIR')) {
            static::addAlias('conf', PROJECT_DIR . DIRECTORY_SEPARATOR . 'conf');
            static::addAlias('errors', PROJECT_DIR . DIRECTORY_SEPARATOR . 'errors');
            static::addAlias('keydb', PROJECT_DIR . DIRECTORY_SEPARATOR . 'keydb');
            static::addAlias('lang', PROJECT_DIR . DIRECTORY_SEPARATOR . 'lang');
            static::addAlias('modules', PROJECT_DIR . DIRECTORY_SEPARATOR . 'modules');
            static::addAlias('router', PROJECT_DIR . DIRECTORY_SEPARATOR . 'router');
            static::addAlias('views', PROJECT_DIR . DIRECTORY_SEPARATOR . 'views');
        }
    }

    /**
     * load
     *
     * @param string $className class name
     *
     * @return boolean
     */
    static public function load($className)
    {
        $file      = '';
        $className = trim($className, '\\');
        if (in_array($className, static::$singleMap)) {
            $file = realpath(static::$singleMap[$className]);
        }
        $arr  = explode('\\', $className);
        $name = array_pop($arr);
        if (!empty($arr)) {
            $nsArr     = $arr;
            $namespace = '';
            while ($ns        = array_shift($nsArr)) {
                if ($namespace == '') {
                    $namespace = $ns;
                } else {
                    $namespace .= '\\' . $ns;
                }
                if (isset(static::$namespaceMap[$namespace])) {
                    foreach (static::$namespaceMap[$namespace] as $dir) {
                        $cdir     = implode(DIRECTORY_SEPARATOR, $nsArr);
                        $path     = $dir . DIRECTORY_SEPARATOR . mb_strtolower($cdir) . DIRECTORY_SEPARATOR . $name . '.php';
                        $realPath = realpath($path);
                        if ($realPath) {
                            $file = $realPath;
                            break;
                        }
                    }
                }
            }
        }
        if (!$file) {
            $path = mb_strtolower(implode(DIRECTORY_SEPARATOR, $arr)) . DIRECTORY_SEPARATOR . $name . '.php';
            foreach (static::$dirMap as $dir) {
                $realPath = realpath($dir . DIRECTORY_SEPARATOR . $path);
                if ($realPath) {
                    $file = $realPath;
                    break;
                }
            }
        }
        if ($file) {
            return static::loadFile($file);
        }
        return false;
    }

    /**
     * loadFile
     *
     * @param string $file file path
     *
     * @return boolean
     */
    static public function loadFile($file)
    {
        if (!file_exists($file)) {
            return false;
        }
        include_once $file;
        return true;
    }

}
