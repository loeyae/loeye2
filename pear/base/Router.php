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

use ArrayAccess;
use loeye\config\router\ConfigDefinition;
use loeye\error\BusinessException;
use loeye\std\ConfigTrait;

/**
 * Description of Router
 *
 * @author   Zhang Yi <loeyae@gmail.com>
 */
class Router extends \loeye\std\Router
{

    use ConfigTrait;

    public const BUNDLE = 'router';

    private $_router;
    protected $config;

    /**
     * __construct
     *
     * @param string $property property name
     * @throws BusinessException
     */
    public function __construct($property)
    {
        $definition = new ConfigDefinition();
        $this->config = $this->bundleConfig($property, null, $definition);
        $this->_initRouter();
    }

    /**
     * getRouter
     *
     * @param string $key key
     *
     * @return null|array
     */
    public function getRouter($key): ?array
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
     * @return string|null
     */
    public function getRouterKey($url): ?string
    {
        $routerKey = null;
        $path = parse_url($url, PHP_URL_PATH);
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
     * @return string|null
     */
    public function match($url): ?string
    {
        $this->reset();
        $moduleId = null;
        $basePath = '';
        if (defined('BASE_SERVER_URL')) {
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
                $this->setMatchedRule($setting['_path']);
                $this->setMatchedData($matches);
                $moduleId = $setting['module_id'];
                if (!empty($setting['params'])) {
                    foreach ($setting['params'] as $key => $value) {
                        $_REQUEST[$key] = $value;
                    }
                }
                $searchKey = [];
                $replaceKey = [];
                if (preg_match_all('({[\w\-_]+})', $moduleId, $iMatches)) {
                    foreach ($iMatches[0] as $match) {
                        $key = mb_substr($match, 1, -1, '7bit');
                        $searchKey[$key] = $match;
                        $replaceKey[$key] = $key;
                    }
                }
                if (isset($setting['format'])) {
                    $search = array();
                    $replace = array();
                    foreach ($setting['format'] as $key) {
                        $value = filter_var($matches[$key], FILTER_SANITIZE_STRING);
                        if (isset($replaceKey[$key])) {
                            $search[] = $searchKey[$key];
                            $replace[] = $value;
                            unset($replaceKey[$key]);
                            $this->addSetting($key, $value);
                        } else {
                            $this->addPathVariable($key, $value);
                            $_REQUEST[$key] = $value;
                        }
                    }
                    $moduleId = str_replace($search, $replace, $moduleId);
                }
                if (isset($setting['get'])) {
                    $search = array();
                    $replace = array();
                    foreach ($setting['get'] as $key => $pattern) {
                        if (isset($replaceKey[$key]) && filter_has_var(INPUT_GET, $key)) {
                            $pattern = str_replace('#', '\#', $pattern);
                            preg_match('#(?<' . $key . '>' . $pattern . ')#', filter_input(INPUT_GET, $key), $match);
                            if (!empty($match)) {
                                $search[] = $searchKey[$key];
                                $replace[] = $match[$key];
                                $this->addSetting($key, $match[$key]);
                            }
                            unset($replaceKey[$key]);
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
     * @param array $params params
     *
     * @return string
     * @throws Exception
     */
    public function generate($routerName, $params = array()): string
    {
        if (!isset($this->_router[$routerName])) {
            throw new BusinessException(BusinessException::INVALID_CONFIG_SET_MSG,
                BusinessException::INVALID_CONFIG_SET_CODE, ['setting' => 'router: ' . $routerName]
            );
        }
        $query = $params;
        $router = $this->_router[$routerName];
        $search = array(
            '^',
            '$',
            '?',
        );
        $replace = array(
            '',
            '',
            '',
        );
        if (isset($router['format'])) {
            foreach ($router['format'] as $key) {
                if (!isset($params[$key])) {
                    throw new BusinessException(
                        BusinessException::INVALID_PARAMETER_MSG,
                        BusinessException::INVALID_PARAMETER_CODE
                    );
                }
                $search[] = '{' . $key . '}';
                $replace[] = urlencode($params[$key]);
                unset($query[$key]);
            }
        }
        if (defined('BASE_SERVER_URL')) {
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
     * @throws BusinessException
     */
    private function _initRouter(): void
    {
        $config = $this->config->get('routes');
        $this->_router = array();
        if (!empty($config)) {
            foreach ($config as $name => $value) {
                if (!isset($value['path'])) {
                    throw new BusinessException(
                        BusinessException::INVALID_CONFIG_SET_MSG, BusinessException::INVALID_CONFIG_SET_CODE, ['setting' => 'route path']);
                }
                $search = array('#');
                $replace = array('\#');
                $params = array();
                if (isset($value['regex'])) {
                    $matches = array();
                    if (preg_match_all('({[\w\-_]+})', $value['path'], $matches)) {
                        foreach ($matches[0] as $match) {
                            $param = mb_substr($match, 1, -1, '7bit');
                            if (isset($value['regex'][$param])) {
                                $params[] = $param;
                                $search[] = $match;
                                if (strpos($value['regex'][$param], '#') !== false) {
                                    $regex = str_replace('#', '\#', $value['regex'][$param]);
                                    $replace[] = '(?<' . $param . '>' . $regex . ')';
                                } else {
                                    $replace[] = '(?<' . $param . '>' . $value['regex'][$param] . ')';
                                }
                            }
                        }
                    }
                }
                $value['_path'] = '#' . str_replace($search, $replace, $value['path']) . '#';
                $value['format'] = $params;
                $this->_router[$name] = $value;
            }
        }
    }

}
