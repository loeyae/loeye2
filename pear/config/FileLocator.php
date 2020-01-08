<?php

/**
 * FileLocator.php
 *
 * PHP version 7
 *
 * Licensed under the Apache License, Version 2.0 (the "License"),
 * see LICENSE for more details: http://www.apache.org/licenses/LICENSE-2.0.
 *
 * @category PHP
 * @package  LOEYE
 * @author   Zhang Yi <loeyae@gmail.com>
 * @version  2020年1月7日 下午6:12:39
 * @link     https://github.com/loeyae/loeye.git
 */

namespace loeye\config;

use Symfony\Component\Config\Exception\FileLocatorFileNotFoundException;

/**
 * FileLocator
 *
 * @author   Zhang Yi <loeyae@gmail.com>
 */
class FileLocator implements \Symfony\Component\Config\FileLocatorInterface
{

    protected $paths;

    public function __construct($paths)
    {
        $this->paths = (array)$paths;
    }

    /**
     * locate
     * 
     * @param string  $name        name
     * @param string  $currentPath current path
     * @param boolean $first       first
     * @return array|string
     * 
     * @throws \InvalidArgumentException
     * @throws FileLocatorFileNotFoundException
     */
    public function locate($name, $currentPath = null, $first = true)
    {
        if ('' == $name) {
            throw new \InvalidArgumentException('An empty file name is not valid to be located.');
        }

        if ($this->isAbsolutePath($name)) {
            if (!file_exists($name)) {
                throw new FileLocatorFileNotFoundException(sprintf('The file "%s" does not exist.', $name), 0, null, [$name]);
            }

            return $name;
        }

        $paths = $this->paths;

        if (null !== $currentPath) {
            $paths = array_map(function($item) use ($currentPath){
                return $item .\DIRECTORY_SEPARATOR . $currentPath;
            }, $paths);
        }

        $paths = array_unique($paths);
        $filepaths = $notfound = [];

        foreach ($paths as $path) {
            if (@file_exists($file = $path.\DIRECTORY_SEPARATOR.$name)) {
                if (true === $first) {
                    return realpath($file);
                }
                $filepaths[] = realpath($file);
            } else {
                $notfound[] = realpath($file);
            }
        }

        if (!$filepaths) {
            throw new FileLocatorFileNotFoundException(sprintf('The file "%s" does not exist (in: %s).', $name, implode(', ', $paths)), 0, null, $notfound);
        }

        return $filepaths;
    }

    /**
     * Returns whether the file path is an absolute path.
     */
    private function isAbsolutePath(string $file): bool
    {
        if ('/' === $file[0] || '\\' === $file[0]
            || (\strlen($file) > 3 && ctype_alpha($file[0])
                && ':' === $file[1]
                && ('\\' === $file[2] || '/' === $file[2])
            )
            || null !== parse_url($file, PHP_URL_SCHEME)
        ) {
            return true;
        }

        return false;
    }

}
