<?php

/**
 * Router.php
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
use loeye\error\BusinessException;

/**
 * Description of Router
 *
 * @author   Zhang Yi <loeyae@gmail.com>
 */
class Router implements \ArrayAccess
{

    use \loeye\std\ConfigTrait;

    const BUNDLE = 'router';

    private $_router;
    protected $config;

    /**
     * __construct
     *
     * @param string $property property name
     */
    public function __construct($property)
    {
        $this->config = $this->bundleConfig($property);
        $this->_initRouter();
    }

    /**
     * offsetExists
     *
     * @param mixed $offset offset
     *
     * @return boolean
     */
    public function offsetExists($offset)
    {
        $methodList = array(
            'getRouter',
            'getRouterKey',
            'match',
        );
        if (in_array($offset, $methodList) || in_array($offset, $propertyList)) {
            return true;
        }
        return false;
    }

    /**
     * offsetGet
     *
     * @param mixed $offset offset
     *
     * @return mixed
     */
    public function offsetGet($offset)
    {
        switch ($offset) {
            case 'getRouter':
            case 'getRouterKey':
            case 'match':
                return $this->$offset();
            default :
                return null;
        }
    }

    /**
     * offsetSet
     *
     * @param mixed $offset offset
     * @param mixed $value  value
     *
     * @return void
     */
    public function offsetSet($offset, $value)
    {
        return;
    }

    /**
     * offsetUnset
     *
     * @param mixed $offset offset
     *
     * @return void
     */
    public function offsetUnset($offset)
    {
        return;
    }

    /**
     * getRouter
     *
     * @param string $key key
     *
     * @return null|array
     */
    public function getRouter($key)
    {
        if (array_key_exists($key, $this->_router)) {
            return $this->_router[$key];
        }
        return null;
    }

    /**
     * getRouterKey
     *
     * @param string $url url
     *
     * @return string
     */
    public function getRouterKey($url)
    {
        $routerKey = null;
        $path      = parse_url($url, PHP_URL_PATH);
        foreach ($this->_router as $key => $setting) {
            if (empty($setting['module_id'])) {
                continue;
            }
            $matches = array();
            if (isset($setting['_path']) && preg_match($setting['_path'], $path, $matches)) {
                $routerKey = $key;
                break;
            }
        }
        return $routerKey;
    }

    /**
     * match
     *
     * @param string $url url
     *
     * @return string
     */
    public function match($url)
    {
        $moduleId = null;
        $basePath = '';
        if (defined("BASE_SERVER_URL")) {
            $basePath = parse_url(BASE_SERVER_URL, PHP_URL_PATH);
        } elseif (filter_has_var(INPUT_SERVER, 'rUrlPath')) {
            $basePath = filter_input(INPUT_SERVER, 'rUrlPath');
        } elseif (filter_has_var(INPUT_SERVER, 'REDIRECT_rUrlPath')) {
            $basePath = filter_input(INPUT_SERVER, 'REDIRECT_rUrlPath');
        }
        if ($basePath) {
            $len = mb_strlen($basePath);
            $url = mb_substr($url, $len);
        }
        $path = parse_url($url, PHP_URL_PATH) or $path = '/';
        foreach ($this->_router as $setting) {
            if (empty($setting['module_id'])) {
                continue;
            }
            $matches = array();
            if (isset($setting['_path']) && preg_match($setting['_path'], $path, $matches)) {
                $moduleId = $setting['module_id'];
                if (isset($setting['params'])) {
                    foreach ($setting['params'] as $key => $value) {
                        $_REQUEST[$key] = $value;
                    }
                }
                if (isset($setting['format'])) {
                    $search  = array();
                    $replace = array();
                    foreach ($setting['format'] as $key) {
                        if (isset($matches[$key])) {
                            $value     = filter_var($matches[$key], FILTER_SANITIZE_STRING);
                            $search[]  = '{' . $key . '}';
                            $replace[] = $value;
                            if (mb_substr($key, 0, 1, '7bit') != '_') {
                                $_REQUEST[$key] = urldecode($value);
                            }
                        }
                    }
                    $moduleId = str_replace($search, $replace, $moduleId);
                }
                if (isset($setting['get'])) {
                    $search  = array();
                    $replace = array();
                    foreach ($setting['get'] as $key => $pattern) {
                        if (filter_has_var(INPUT_GET, $key)) {
                            $matche  = array();
                            $pattern = str_replace('#', '\#', $pattern);
                            preg_match('#(?<' . $key . '>' . $pattern . ')#', filter_input(INPUT_GET, $key), $matche);
                            if (!empty($matche)) {
                                $search[]  = '{' . $key . '}';
                                $replace[] = $matche[$key];
                            }
                        }
                    }
                    $moduleId = str_replace($search, $replace, $moduleId);
                }
                break;
            }
        }
        return $moduleId;
    }

    /**
     * generate
     *
     * @param string $routerName router name
     * @param array  $params     params
     *
     * @return string
     * @throws Exception
     */
    public function generate($routerName, $params = array())
    {
        if (!isset($this->_router[$routerName])) {
            throw new Exception(
                    "router name: ${routerName} 不存在",
                    Exception::INVALID_CONFIG_SET_CODE
            );
        }
        $query   = $params;
        $router  = $this->_router[$routerName];
        $search  = array(
            '^',
            '$',
            '?',
        );
        $replace = array(
            '',
            '',
            '',
        );
        if (isset($router['_params'])) {
            foreach ($router['_params'] as $key) {
                if (!isset($params[$key])) {
                    throw new BusinessException(
                        BusinessException::INVALID_PARAMETER_MSG,
                        BusinessException::INVALID_PARAMETER_CODE
                    );
                }
                $search[]  = '{' . $key . '}';
                $replace[] = urlencode($params[$key]);
                unset($query[$key]);
            }
        }
        if (defined("BASE_SERVER_URL")) {
            $url = BASE_SERVER_URL;
        } else {
            $url = '';
        }
        $url .= str_replace($search, $replace, $router['path']);
        if (isset($router['params'])) {
            foreach ($router['params'] as $key => $value) {
                $query[$key] = $value;
            }
        }
        if (isset($query['#'])) {
            $mark = $query['#'];
            unset($query['#']);
        }
        if (count($query) > 0) {
            $url .= '?' . http_build_query($query);
        }
        if (isset($mark)) {
            $url .= '#' . $mark;
        }
        return filter_var($url, FILTER_SANITIZE_URL);
    }

    /**
     * _initRouter
     *
     * @return void
     */
    private function _initRouter()
    {
        $config        = $this->config->get('routes');
        $this->_router = array();
        if (!empty($config)) {
            foreach ($config as $name => $value) {
                if (!isset($value['path'])) {
                    throw new BusinessException(
                        BusinessException::INVALID_CONFIG_SET_MSG, BusinessException::INVALID_CONFIG_SET_CODE);
                }
                $search  = array('#');
                $replace = array('\#');
                $params  = array();
                if (isset($value['regex'])) {
                    $matches = array();
                    if (preg_match_all('(\{[\w\d\-\_]+\})', $value['path'], $matches)) {
                        foreach ($matches[0] as $match) {
                            $param = mb_substr($match, 1, -1, '7bit');
                            if (isset($value['regex'][$param])) {
                                $params[] = $param;
                                $search[] = $match;
                                if (strpos($value['regex'][$param], '#') !== false) {
                                    $regex     = str_replace('#', '\#', $value['regex'][$param]);
                                    $replace[] = '(?<' . $param . '>' . $regex . ')';
                                } else {
                                    $replace[] = '(?<' . $param . '>' . $value['regex'][$param] . ')';
                                }
                            }
                        }
                    }
                }
                $value['_path']       = '#' . str_replace($search, $replace, $value['path']) . '#';
                $value['format']      = $params;
                $this->_router[$name] = $value;
            }
        }
    }

}
