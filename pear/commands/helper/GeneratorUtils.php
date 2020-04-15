<?php
/**
 * GeneratorUtils.php
 *
 * PHP version 7
 *
 * Licensed under the Apache License, Version 2.0 (the "License"),
 * see LICENSE for more details: http://www.apache.org/licenses/LICENSE-2.0.
 *
 * @category PHP
 * @package  LOEYE
 * @author   Zhang Yi <loeyae@gmail.com>
 * @version  2020/4/15 10:52
 * @link     https://github.com/loeyae/loeye2.git
 */


namespace loeye\commands\helper;

use RuntimeException;

/**
 * Class GeneratorUtils
 * @package loeye\commands\helper
 */
class GeneratorUtils
{


    /**
     * getNamespace
     *
     * @param string $destDir
     * @return string
     */
    public static function getNamespace($destDir): string
    {
        $dir = substr($destDir, strlen(PROJECT_DIR) + 1);
        return PROJECT_NAMESPACE . '\\' . $dir;
    }

    /**
     * writeFile
     *
     * @param string $outputDirectory
     * @param string $className
     * @param string $code
     * @param string $force
     */
    public static function writeFile($outputDirectory, $className, $code, $force): void
    {
        $path = $outputDirectory . D_S
            . str_replace('\\', D_S, $className) . '.php';
        $dir  = dirname($path);

        if (!file_exists($dir) && !mkdir($dir, 0775, true) && !is_dir($dir)) {
            throw new RuntimeException(sprintf('Directory "%s" was not created', $dir));
        }

        if ($force || !file_exists($path)) {
            file_put_contents($path, $code);
            chmod($path, 0664);
        }
    }

    /**
     * generateTemplate
     *
     * @param array $variables
     * @param string $template
     *
     * @return string
     */
    public static function generateTemplate(array $variables, string $template): string
    {
        return str_replace(array_keys($variables), array_values($variables), $template);
    }

    /**
     * getClassName
     *
     * @param string $fullClassName
     * @return bool|string
     */
    public static function getClassName(string $fullClassName)
    {
        $pos = strrpos($fullClassName, '\\');
        if ($pos === false) {
            return $fullClassName;
        }
        return substr( $fullClassName,$pos + 1);
    }

}