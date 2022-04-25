<?php

/**
 * FuncLibraries.php
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

namespace loeye\lib;

use loeye\base\Cache;
use loeye\base\Context;
use loeye\base\Exception;
use Psr\Cache\InvalidArgumentException;
use Symfony\Component\Cache\Exception\CacheException;

/**
 * FuncLibraries
 *
 * @author   Zhang Yi <loeyae@gmail.com>
 */
class FuncLibraries
{

    /**
     * lyHasError
     *
     * @param Context $context context
     * @param array $params params
     *
     * @return boolean
     */
    public static function lyHasError(Context $context, $params = array()): bool
    {
        if (!empty($params)) {
            foreach ((array)$params as $errorKey) {
                $errors = $context->getErrors($errorKey);
                if (!empty($errors)) {
                    return true;
                }
            }
        } else {
            $errors = $context->getErrors();
            return !empty($errors);
        }
        return false;
    }

    /**
     * lyGetError
     *
     * @param Context $context context
     * @param array $params params
     *
     * @return mixed
     */
    public static function lyGetError(Context $context, $params = array())
    {
        if (!empty($params)) {
            $data = array();
            foreach ((array)$params as $errorKey) {
                $errors = $context->getErrors($errorKey);
                if (!empty($errors)) {
                    $init = $data;
                    $data = array_merge_recursive($init, $errors);
                }
            }
            return $data;
        }

        $errors = $context->getErrors();
        return $errors;
    }

    /**
     * lyCallback
     *
     * @param Context $context context
     * @param array $params params
     *
     * @return boolean
     */
    public static function lyCallback(Context $context, $params = array()): bool
    {
        if (!empty($params)) {
            $params = (array)$params;
            $funName = array_shift($params);
            $parameter = array();
            if (!empty($params)) {
                foreach ($params as $key) {
                    $parameter[] = $context->get($key);
                }
            }
            return call_user_func_array($funName, $parameter);
        }
        return false;
    }

    /**
     * lyHasPageCache
     *
     * @param Context $context context
     * @param array $params params
     *
     * @return bool
     * @throws InvalidArgumentException
     * @throws CacheException
     * @throws Exception
     */
    public static function lyHasPageCache(Context $context, $params = array()): bool
    {
        $fileKey = self::getFileKey($context, $params);
        $cache = new Cache($context->getAppConfig());
        $content = $cache->get($fileKey);
        return !empty($content);
    }

    /**
     * lyGetPageCache
     *
     * @param Context $context context
     * @param array $params params
     *
     * @return mixed
     * @throws CacheException
     * @throws Exception
     * @throws InvalidArgumentException
     */
    public static function lyGetPageCache(Context $context, $params = array())
    {
        $fileKey = self::getFileKey($context, $params);
        $cache = new Cache($context->getAppConfig());
        return $cache->get($fileKey);
    }

    /**
     * getFileKey
     *
     * @param Context $context
     * @param array $params
     *
     * @return string
     */
    protected static function getFileKey(Context $context, $params = []): string
    {
        $data = [];
        if (!empty($params)) {
            foreach ((array)$params as $key) {
                if (isset($_REQUEST[$key])) {
                    $data[$key] = $_REQUEST[$key];
                }
            }
        }

        $fileKey = $context->getRequest()->getModuleId();
        if (!empty($data)) {
            sort($data);
            $fileKey .= '?' . http_build_query($data);
        }
        return $fileKey;
    }

    /**
     * lyHasContextCache
     *
     * @param Context $context context
     * @param array $params params
     *
     * @return bool
     */
    public static function lyHasContextCache(Context $context, $params = array()): bool
    {
        if (!empty($params)) {
            foreach ((array)$params as $key) {
                $ret = $context->isExpire($key);
                if ($ret !== false) {
                    return false;
                }
            }
        }
        return true;
    }

    /**
     * lyGetContextCache
     *
     * @param Context $context context
     * @param array $params params
     *
     * @return array
     */
    public static function lyGetContextCache(Context $context, $params = array()): array
    {
        $data = [];
        if (!empty($params)) {
            foreach ((array)$params as $key) {
                if ($context->isExpire($key) === false) {
                    $data[$key] = $context->get($key);
                }
            }
        }
        return $data;
    }


    /**
     * lyIsAjax
     *
     * @param Context $context
     * @return bool
     */
    public static function lyIsAjax(Context $context): bool
    {
        return $context->getRequest()->isAjaxRequest;
    }

    /**
     * lyHasMethod
     *
     * @param Context $context
     * @param $methods
     * @return bool
     */
    public static function lyHasMethod(Context $context, $methods = array()): bool
    {
        if (empty($methods)) {
            return false;
        }
        $methods = array_map("strtoupper", $methods);
        return in_array($context->getRequest()->getMethod(), $methods);
    }
}
