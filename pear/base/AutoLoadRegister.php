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
 * @version  2018-07-23 22:44:28
 * @link     https://github.com/loeyae/loeye2.git
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
     * @param string $ns namespace
     * @param string $path dir path
     * @param bool $prepend prepend
     *
     * @return boolean
     */
    public static function addNamespace($ns, $path, $prepend = false): bool
    {
        $path = static::realAliasFile($path);
        $maps = static::$namespaceMap[$ns] ?? [];
        if (in_array($path, $maps, true) || !is_dir($path)) {
            return false;
        }
        if ($prepend) {
            array_unshift($maps, $path);
        } else {
            $maps[] = $path;
        }
        static::$namespaceMap[$ns] = $maps;
        return true;
    }

    /**
     * addFile
     *
     * @param string $file file path
     * @param bool $prepend prepend
     *
     * @return boolean
     */
    public static function addFile($file, $prepend = false): bool
    {
        $file = static::realAliasFile($file);
        if (in_array($file, static::$fileMap, true) || !file_exists($file)) {
            return false;
        }
        if ($prepend) {
            array_unshift(static::$fileMap, $file);
        } else {
            static::$fileMap[] = $file;
        }
        return true;
    }

    /**
     * addFile
     *
     * @param string $dir dir path
     * @param bool $prepend prepend
     *
     * @return boolean
     */
    public static function addDir($dir, $prepend = false): bool
    {
        $dir = static::realAliasFile($dir);
        if (in_array($dir, static::$dirMap, true) || !is_dir($dir)) {
            return false;
        }
        if ($prepend) {
            array_unshift(static::$dirMap, $dir);
        } else {
            static::$dirMap[] = $dir;
        }
        return true;
    }

    /**
     * addAlias
     *
     * @param string $alias alias name
     * @param string $path dir path
     * @param bool $prepend prepend
     *
     * @return boolean
     */
    public static function addAlias($alias, $path, $prepend = false): bool
    {
        $path = static::realAliasFile($path);
        $maps = static::$aliasMap[$alias] ?? [];
        if (in_array($path, $maps, true) || !is_dir($path)) {
            return false;
        }
        if ($prepend) {
            array_unshift($maps, $path);
        } else {
            $maps[] = $path;
        }
        static::$aliasMap[$alias] = $maps;
        return true;
    }

    /**
     * addSingle
     *
     * @param string $className class name
     * @param string $file file path
     *
     * @return boolean
     */
    public static function addSingle($className, $file): bool
    {
        $file = static::realAliasFile($file);
        $className = trim($className, '\\');
        if (in_array($className, static::$singleMap, true) || !file_exists($file)) {
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
    public static function loadAlias($file): bool
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
     * @return mixed
     */
    public static function realAliasFile($file)
    {
        $file = str_replace('\\', '/', $file);
        if (mb_strpos($file, '@') !== 0) {
            return $file;
        }
        $s = mb_strpos($file, '/');
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
    public static function autoLoad(): void
    {
        foreach (static::$fileMap as $file) {
            static::loadFile($file);
        }
    }

    /**
     * initApp
     * @return void
     */
    public static function initApp(): void
    {
        if (!defined('PROJECT_DIR')) {
            $debugTrace = debug_backtrace();
            if (isset($debugTrace[1])) {
                $appBaseDir = realpath(dirname($debugTrace[1]['file']) . D_S . PROJECT_NAMESPACE);
                define('PROJECT_DIR', $appBaseDir);
            }
        }
        if (defined('PROJECT_DIR')) {
            static::addAlias('conf', PROJECT_DIR . DIRECTORY_SEPARATOR . 'conf');
            static::addAlias('errors', PROJECT_DIR . DIRECTORY_SEPARATOR . 'errors');
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
    public static function load($className): bool
    {
        $file = '';
        $className = trim($className, '\\');
        if (array_key_exists($className, static::$singleMap)) {
            $file = realpath(static::$singleMap[$className]);
            return static::loadFile($file);
        }
        $arr = explode('\\', $className);
        $name = array_pop($arr);
        if (!empty($arr)) {
            $nsArr = $arr;
            $namespace = '';
            while ($ns = array_shift($nsArr)) {
                if ($namespace === '') {
                    $namespace = $ns;
                } else {
                    $namespace .= '\\' . $ns;
                }
                if (isset(static::$namespaceMap[$namespace])) {
                    foreach (static::$namespaceMap[$namespace] as $dir) {
                        $cdir = implode(DIRECTORY_SEPARATOR, $nsArr);
                        $path = $dir . DIRECTORY_SEPARATOR . mb_strtolower($cdir) . DIRECTORY_SEPARATOR . $name . '.php';
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
    public static function loadFile($file): bool
    {
        if (!file_exists($file)) {
            return false;
        }
        include_once $file;
        return true;
    }

}
