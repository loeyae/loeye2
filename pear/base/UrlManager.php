<?php

/**
 * UrlManager.php
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
 * UrlManager
 *
 * @author   Zhang Yi <loeyae@gmail.com>
 */
class UrlManager
{

    private $_rule;

    const REWRITE_KEY_PREFIX = 'rwt:';

    public function __construct($setting)
    {
        $this->_rule = $setting;
    }

    /**
     * match
     *
     * @param string $url url
     *
     * @return mixed
     */
    public function match($url)
    {
        if (defined("BASE_SERVER_URL")) {
            $url = str_replace(BASE_SERVER_URL, '', $url);
        }
        $path = parse_url($url, PHP_URL_PATH) or $path = '/';
        foreach ($this->_rule as $key => $item) {
            if (strpos($key, '#') !== false) {
                $key = '#' . str_replace('#', '\#', $key);
            }
            $pattern = '#^' . preg_replace('#<([\w\d-\_]+):([^>]+)>#', '(?\'$1\'$2)', $key) . '$#';
            $matches = [];
            if (preg_match($pattern, $path, $matches)) {
                $imatches   = [];
                $search     = [];
                $replace    = [];
                $replaceKey = [];
                if (preg_match_all('(\{[\w\d\-\_]+\})', $item, $imatches)) {
                    foreach ($imatches[0] as $match) {
                        $search[]     = $match;
                        $replaceKey[] = mb_substr($match, 1, -1, '7bit');
                    }
                }
                if (!empty($replaceKey)) {
                    foreach ($matches as $pkey => $value) {
                        if (is_numeric($pkey)) {
                            continue;
                        }
                        $rkeys = array_keys($replaceKey, $pkey);
                        if (!empty($rkeys)) {
                            $replace[$rkeys[0]]                        = $value;
                            $_REQUEST[self::REWRITE_KEY_PREFIX . $pkey] = $value;
                            unset($replaceKey[$rkeys[0]]);
                        } else {
                            $_REQUEST[$pkey] = $value;
                        }
                    }
                    if (!empty($replaceKey)) {
                        return null;
                    }
                    return str_replace($search, $replace, $item);
                }
                return $item;
            }
        }
        if ($path == '/') {
            return null;
        }
        return false;
    }

    /**
     * generate
     *
     * @param array $params params
     *
     * @return string
     */
    public function generate($params = array())
    {
        $url = '';
        if (defined("BASE_SERVER_URL")) {
            $url = BASE_SERVER_URL;
        }
        if (empty($params)) {
            return $url . '/';
        }
        foreach ($this->_rule as $key => $null) {
            $matches = [];
            if (preg_match_all('#<([\w\d-\_]+):([^>]+)>#', $key, $matches)) {
                $search     = $matches[0];
                $replaceKey = $matches[1];
                $validate   = $matches[2];
                $replace    = [];
                foreach ($replaceKey as $index => $mkey) {
                    if (!isset($params[$mkey])) {
                        break;
                    }
                    if (!preg_match('#' . $validate[$index] . '#', $params[$mkey])) {
                        break;
                    }
                    $replace[$index] = $params[$mkey];
                    unset($params[$mkey]);
                }
                if (count($replace) !== count($search)) {
                    continue;
                }
                $url .= str_replace($search, $replace, $key);
                if (!empty($params)) {
                    $queryStr = http_build_query($params);
                    $pos      = mb_strpos($url, '?');
                    $len      = mb_strlen($url);
                    if ($pos === false) {
                        $url .= "?${queryStr}";
                    } else if ($pos < ($len - 1)) {
                        $url .= "&${queryStr}";
                    } else {
                        $url .= $queryStr;
                    }
                }
                return $url;
            }
        }
        return null;
    }

}
