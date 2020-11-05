<?php

/**
 * ConfigResource.php
 *
 * PHP version 7
 *
 * Licensed under the Apache License, Version 2.0 (the "License"),
 * see LICENSE for more details: http://www.apache.org/licenses/LICENSE-2.0.
 *
 * @category PHP
 * @package  LOEYE
 * @author   Zhang Yi <loeyae@gmail.com>
 * @version  2020年1月17日 下午10:57:49
 * @link     https://github.com/loeyae/loeye.git
 */

namespace loeye\config;

use FilesystemIterator;
use InvalidArgumentException;
use loeye\lib\Secure;
use LogicException;
use RecursiveCallbackFilterIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;
use Symfony\Component\Config\Resource\SelfCheckingResourceInterface;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\Glob;
use Traversable;

/**
 * ConfigResource
 *
 * @author   Zhang Yi <loeyae@gmail.com>
 */
class ConfigResource implements SelfCheckingResourceInterface {

    private $resource;
    private $pattern;
    private $globBrace;
    private $hash;

    /**
     * @param string      $resource The file path to the resource
     * @param string|null $pattern  A pattern to restrict monitored files
     *
     * @throws InvalidArgumentException
     */
    public function __construct(string $resource, string $pattern = null)
    {
        $this->resource = realpath($resource) ? $resource : false;
        $this->pattern = $pattern;
        $this->globBrace = defined('GLOB_BRACE') ? GLOB_BRACE : 0;

        if (false === $this->resource || !is_dir($this->resource)) {
            throw new InvalidArgumentException(sprintf('The directory "%s" does not exist.', $resource));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function __toString()
    {
        return Secure::getKey([$this->resource, $this->pattern]);
    }

    /**
     * @return string The file path to the resource
     */
    public function getResource(): string
    {
        return $this->resource;
    }

    /**
     * Returns the pattern to restrict monitored files.
     *
     * @return string|null
     */
    public function getPattern(): ?string
    {
        return $this->pattern;
    }


    /**
     * {@inheritdoc}
     */
    public function isFresh($timestamp = null): bool
    {
        $hash = $this->computeHash();

        if (null === $this->hash) {
            $this->hash = $hash;
            return true;
        }

        return $this->hash !== $hash;
    }

    /**
     * __sleep
     *
     * @return array
     */
    public function __sleep(): array
    {
        if (null === $this->hash) {
            $this->hash = $this->computeHash();
        }

        return ['resource', 'pattern', 'hash', 'globBrace'];
    }

    /**
     * getIterator
     *
     * @return Traversable
     */
    public function getIterator()
    {
        if (!file_exists($this->resource)) {
            return;
        }
        $resource = str_replace('\\', '/', $this->resource);
        $paths = null;

        if (0 !== strpos($resource, 'phar://') && false === strpos($this->pattern, '/**/')) {
            if ($this->globBrace || false === strpos($this->pattern, '{')) {
                $paths = glob($this->resource.$this->pattern, GLOB_NOSORT | $this->globBrace);
            } elseif (false === strpos($this->pattern, '\\') || !preg_match('/\\\\[,{}]/', $this->pattern)) {
                foreach ($this->expandGlob($this->pattern) as $p) {
                    $paths[] = glob($this->resource.$p, GLOB_NOSORT);
                }
                $paths = array_merge(...$paths);
            }
        }

        if (null !== $paths) {
            sort($paths);
            foreach ($paths as $path) {

                if (is_file($path)) {
                    yield $path => new SplFileInfo($path);
                }
                if (!is_dir($path)) {
                    continue;
                }
                $files = iterator_to_array(new RecursiveIteratorIterator(
                    new RecursiveCallbackFilterIterator(
                        new RecursiveDirectoryIterator($path, FilesystemIterator::SKIP_DOTS |
                            FilesystemIterator::FOLLOW_SYMLINKS),
                        static function (SplFileInfo $file) {
                            return '.' !== $file->getBasename()[0];
                        }
                    ),
                    RecursiveIteratorIterator::LEAVES_ONLY
                ));
                uasort($files, 'strnatcmp');

                foreach ($files as $key => $info) {
                    if ($info->isFile()) {
                        yield $key => $info;
                    }
                }
            }

            return;
        }

        if (!class_exists(Finder::class)) {
            throw new LogicException(sprintf('Extended glob pattern "%s" cannot be used as the Finder component is not installed.', $this->pattern));
        }

        $finder = new Finder();
        $regex = Glob::toRegex($this->pattern);
        $regex = substr_replace($regex, '(/|$)', -2, 1);

        $resourceLen = \strlen($this->resource);
        foreach ($finder->followLinks()->sortByName()->in($this->resource) as $path => $info) {
            $normalizedPath = str_replace('\\', '/', $path);
            if (!$info->isFile() || !preg_match($regex, substr($normalizedPath, $resourceLen))) {
                continue;
            }

            yield $path => $info;
        }
    }

    private function computeHash(): string
    {
        $hash = hash_init('md5');

        foreach ($this->getIterator() as $path => $info) {
            hash_update($hash, $path.$info->getMTime()."\n");
        }

        return hash_final($hash);
    }

    /**
     * expandGlob
     *
     * @param string $pattern
     * @return array
     */
    private function expandGlob(string $pattern): array
    {
        $segments = preg_split('/{([^{}]*+)}/', $pattern, -1, PREG_SPLIT_DELIM_CAPTURE);
        $paths = [$segments[0]];
        $patterns = [];

        for ($i = 1, $iMax = count($segments); $i < $iMax; $i += 2) {
            $patterns = [];

            foreach (explode(',', $segments[$i]) as $s) {
                foreach ($paths as $p) {
                    $patterns[] = $p.$s.$segments[1 + $i];
                }
            }

            $paths = $patterns;
        }

        $j = 0;
        foreach ($patterns as $i => $p) {
            if (false !== strpos($p, '{')) {
                $p = $this->expandGlob($p);
                array_splice($paths, $i + $j, 1, $p);
                $j += \count($p) - 1;
            }
        }

        return $paths;
    }

}
