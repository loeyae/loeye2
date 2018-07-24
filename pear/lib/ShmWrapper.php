<?php

/**
 * ShmWrapper.php
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

namespace loeye\lib;

/**
 * ShmWrapper
 *
 * @author   Zhang Yi <loeyae@gmail.com>
 */
class ShmWrapper
{

    /**
     * shared memory resource handle
     *
     * @var resource $shmId
     */
    protected $shmId;
    protected $key;

    /**
     * __construct
     *
     * @param string $pathname path name
     * @param string $proj     project id, This must be a one character string
     *
     * @return void
     */
    public function __construct($pathname, $proj)
    {
        $key         = ftok($pathname, $proj);
        $this->key   = $key;
        $this->shmId = shm_attach($key);
    }

    /**
     * getInstance
     *
     * @param string $pathname path name
     * @param string $proj     project id, This must be a one character string
     * @param int    $memsize  memory size
     * @param int    $perm     permission bits. Default to 0666
     *
     * @return \self
     */
    public static function getInstance($pathname, $proj, $memsize = 10000, $perm = 0666)
    {
        return new self($pathname, $proj, $memsize, $perm);
    }

    /**
     * isAble
     *
     * @return boolean
     */
    public static function isAble()
    {
        if (function_exists('ftok') && function_exists('shm_attach') && PROJECT_CONF_CACHE_ENABLE && PROJECT_CONF_MEM_CACHE_ENABLE) {
            return true;
        }
        return false;
    }

    /**
     * detach
     * <p>
     * Disconnects from shared memory segment
     * </p>
     *
     * @return void
     */
    public function detach()
    {
        return shm_detach($this->shmId);
    }

    /**
     * remove
     * <p>
     * Remove a semaphore
     * </p>
     *
     * @return void
     */
    public function remove()
    {
        return shm_remove($this->shmId);
    }

    /**
     * put
     *
     * @param int   $key   key
     * @param mixed $value value
     *
     * @return void
     */
    public function put($key, $value)
    {
        $this->remove();
        $shmData = $value;
        if (is_bool($value)) {
            $shmData = new ShmDataWrapper($value);
        }
        $content     = FileCache::compress($shmData);
        $len         = strlen($content) + 128;
        $this->shmId = shm_attach($key, $len);
        return shm_put_var($this->shmId, $key, $value);
    }

    /**
     * get
     *
     * @param int $key key
     *
     * @return mixed
     */
    public function get($key)
    {
        if (!shm_has_var($this->shmId, $key)) {
            return null;
        }
        $data = shm_get_var($this->shmId, $key);
        $item = FileCache::compress($data, false);
        if ($item instanceof ShmDataWrapper) {
            return $item->var;
        }
        return $item;
    }

    /**
     * isExists
     *
     * @param int $key key
     *
     * @return bool
     */
    public function isExists($key)
    {
        return shm_has_var($this->shmId, $key);
    }

    /**
     * remveKey
     *
     * @param int $key key
     *
     * @return bool
     */
    public function remveKey($key)
    {
        return shm_remove_var($this->shmId, $key);
    }

}

/**
 * ShmDataWrapper
 *
 * @author   Zhang Yi <loeyae@gmail.com>
 */
class ShmDataWrapper
{

    public $var;

    /**
     * __construct
     *
     * @param mixed $value value
     */
    public function __construct($value)
    {
        $this->var = $value;
    }

}
