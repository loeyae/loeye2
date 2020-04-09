<?php

/**
 * ConfigCache.php
 *
 * PHP version 7
 * 
 * Licensed under the Apache License, Version 2.0 (the "License"),
 * see LICENSE for more details: http://www.apache.org/licenses/LICENSE-2.0.
 * 
 * @category PHP
 * @package  LOEYE
 * @author   Zhang Yi <loeyae@gmail.com>
 * @version  2020年1月17日 下午10:06:19
 * @link     https://github.com/loeyae/loeye.git
 */

namespace loeye\config;

use Psr\Cache\InvalidArgumentException;
use \Symfony\Component\Cache\Adapter\PhpFilesAdapter;
use Symfony\Component\Cache\Exception\CacheException;
use Symfony\Component\Config\Resource\GlobResource;
use \Symfony\Component\Filesystem\Exception\IOException;
use \Symfony\Component\Filesystem\Filesystem;

/**
 * ConfigCache
 *
 * @author   Zhang Yi <loeyae@gmail.com>
 */
class ConfigCache {

    /**
     * ConfigResource
     * 
     * @var ConfigResource
     */
    protected $resource;

    /**
     *
     * @var PhpFilesAdapter
     */
    protected $cacheAdapter;
    protected $directory;
    protected $metaFile;
    protected $pattern;
    protected $namespace;
    protected $needCommit = false;


    /**
     * __construct
     *
     * @param string $path
     * @param string $cacheDir
     * @param string $namespace
     * @param PhpFilesAdapter|null $cacheAdapter
     * @param ConfigResource $resource
     * @throws CacheException
     */
    public function __construct($path, $cacheDir, $namespace, PhpFilesAdapter $cacheAdapter = null, ConfigResource $resource = null)
    {
        $this->namespace = $namespace;
        $this->pattern   = $this->nsToPattern();
        $this->directory = $cacheDir;
        if (null === $cacheAdapter) {
            $cacheAdapter = new PhpFilesAdapter($namespace, 0, $this->directory);
        }
        $this->cacheAdapter = $cacheAdapter;
        if (null === $resource) {
            $resource = new ConfigResource($path, $this->pattern);
        }
        $this->resource = $resource;
        $this->metaFile = $this->getMetaFile();
        $this->getResourceByMetaFile();
    }
    
    public function __destruct()
    {
        if ($this->needCommit) {
            $this->cacheAdapter->commit();
        }
    }


    /**
     * nsToPattern
     * 
     * @return string
     */
    public function nsToPattern(): string
    {
        return DIRECTORY_SEPARATOR . strtr($this->namespace, ['.' => '/', '_' => '/', '-' => '/']);
    }


    /**
     * getMetaFile
     * 
     * @return string
     */
    public function getMetaFile(): string
    {
        return $this->directory . DIRECTORY_SEPARATOR . $this->namespace . DIRECTORY_SEPARATOR . $this->resource . '.meta';
    }


    /**
     * getResourceByMetaFile
     * 
     * @return GlobResource
     */
    public function getResourceByMetaFile(): GlobResource
    {
        $resource = null;
        if (file_exists($this->metaFile)) {
            $content  = file_get_contents($this->metaFile);
            if ($resource = unserialize($content, null)) {
                $this->resource = $resource;
            }
        }
        return $this->resource;
    }


    /**
     * isFresh
     * 
     * @return bool
     */
    public function isFresh(): bool
    {
        return $this->resource->isFresh(0);
    }


    /**
     * write
     *
     * @param array $contents
     * @throws InvalidArgumentException
     */
    public function write(array $contents): void
    {
        $mode       = 0666;
        $umask      = umask();
        $filesystem = new Filesystem();
        $filesystem->dumpFile($this->metaFile, serialize($this->resource));
        try {
            $filesystem->chmod($this->getMetaFile(), $mode, $umask);
        } catch (IOException $e) {
            // discard chmod failure (some filesystem may not support it)
        }
        foreach ($contents as $key => $value) {
            $item = $this->cacheAdapter->getItem($key);
            $item->set($value);
            $this->cacheAdapter->saveDeferred($item);
        }
        $this->cacheAdapter->commit();
    }


    /**
     * save
     *
     * @param string $key
     * @param mixed $value
     * @throws InvalidArgumentException
     */
    public function save($key, $value): void
    {
        $item = $this->cacheAdapter->getItem($key);
        $item->set($value);
        $this->cacheAdapter->saveDeferred($item);
        $this->needCommit = true;
    }
    
    
    /**
     * commit
     */
    public function commit(): void
    {
        $this->cacheAdapter->commit();
        $this->needCommit = false;
    }


    /**
     * 
     * @return PhpFilesAdapter
     */
    public function cacheAdapter(): PhpFilesAdapter
    {
        return $this->cacheAdapter;
    }

}
