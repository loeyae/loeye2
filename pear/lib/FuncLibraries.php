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
     * @param \loeye\base\Context $context context
     * @param array               $params  params
     *
     * @return boolean
     */
    static public function lyHasError(\loeye\base\Context $context, $params = array())
    {
        if (!empty($params)) {
            foreach ((array) $params as $errorKey) {
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
     * @param \loeye\base\Context $context context
     * @param array               $params  params
     *
     * @return mixed
     */
    static public function lyGetError(\loeye\base\Context $context, $params = array())
    {
        if (!empty($params)) {
            $data = array();
            foreach ((array) $params as $errorKey) {
                $errors = $context->getErrors($errorKey);
                if (!empty($errors)) {
                    $data = array_merge_recursive($data, $errors);
                }
            }
            return $data;
        } else {
            $errors = $context->getErrors();
            return $errors;
        }
        return null;
    }

    /**
     * lyCallback
     *
     * @param \loeye\base\Context $context context
     * @param array               $params  params
     *
     * @return boolean
     */
    static public function lyCallback(\loeye\base\Context $context, $params = array())
    {
        if (!empty($params)) {
            $params    = (array) $params;
            $funcname  = array_shift($params);
            $parameter = array();
            if (!empty($params)) {
                foreach ($params as $key) {
                    $parameter[] = $context->get($key);
                }
            }
            return call_user_func_array($funcname, $parameter);
        }
        return false;
    }

    /**
     * lyHasPageCache
     *
     * @param \loeye\base\Context $context context
     * @param array               $params  params
     *
     * @return type
     */
    static public function lyHasPageCache(\loeye\base\Context $context, $params = array())
    {
        $data = [];
        if (!empty($params)) {
            foreach ((array) $params as $key) {
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
        $cache   = new \loeye\base\Cache($context->getAppConfig());
        $content = $cache->get($fileKey);
        return $content != false;
    }

    /**
     * lyGetPageCache
     *
     * @param \loeye\base\Context $context context
     * @param array               $params  params
     *
     * @return type
     */
    static public function lyGetPageCache(\loeye\base\Context $context, $params = array())
    {
        $data = [];
        if (!empty($params)) {
            foreach ((array) $params as $key) {
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
        $cache   = new SimpleFileCache($context->getAppConfig()->getPropertyName() . '/temp', $fileKey);
        $content = $cache->get('source');
        return $content;
    }

    /**
     * lyHasContextCache
     *
     * @param \loeye\base\Context $context context
     * @param array               $params  params
     *
     * @return type
     */
    static public function lyHasContextCache(\loeye\base\Context $context, $params = array())
    {
        if (!empty($params)) {
            foreach ((array) $params as $key) {
                $ret = $context->isExpire($key);
                if ($ret != false) {
                    return false;
                }
            }
        }
        return true;
    }

    /**
     * lyGetContextCache
     *
     * @param \loeye\base\Context $context context
     * @param array               $params  params
     *
     * @return type
     */
    static public function lyGetContextCache(\loeye\base\Context $context, $params = array())
    {
        $data = [];
        if (!empty($params)) {
            foreach ((array) $params as $key) {
                if ($context->isExpire($key) == false) {
                    $data[$key] = $context->get($key);
                }
            }
        }
        return $data;
    }

}
