<?php

/**
 * Request.php
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

namespace loeye\std;

/**
 * Request
 *
 * @author   Zhang Yi <loeyae@gmail.com>
 */
class Request implements \ArrayAccess
{

    private $_lang = 'zh_CN';
    private $_country = 'cn';

    /**
     *
     * @var \GuzzleHttp\Psr7\Uri
     */
    private $_uri;
    private $_moduleId;
    private $_browser;
    private $_device;
    protected $_allowedFormatType = array();
    private $isAjaxRequest;
    public $isHttps;
    public $isFlashRequest;
    public $requestMethod;

    /**
     * __construct
     *
     * @return void
     */
    public function __construct()
    {
        $moduleId = null;
        $argc     = func_num_args();
        if ($argc > 0) {
            $moduleId = func_get_arg(0);
        }
        $this['moduleId'] = $moduleId;
        $this->_getIsAjaxRequest();
        $this->_getIsFlashRequest();
        $this->_getIsSecureConnection();
        $this->_getRequestType();
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
        $methodList   = array(
            'parameter',
            'get',
            'post',
            'cookie',
            'env',
            'session',
            'server',
            'browser',
            'property',
            'moduleId',
            'device',
            'language',
            'formatType',
            'uri',
        );
        $propertyList = array(
            'isAjaxRequest',
            'isHttps',
            'isFlashRequest',
            'requestMethod',
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
            case 'get':
                return $this->getParameterGet();
            case 'post':
                return $this->getParameterPost();
            case 'parameter':
            case 'cookie':
            case 'env':
            case 'session':
            case 'server':
            case 'browser':
            case 'property':
            case 'moduleId':
            case 'device':
            case 'language':
            case 'formatType':
            case 'uri':
            case 'method':
            case 'requestTime':
            case 'requestTimeFloat':
                $method = 'get' . ucfirst($offset);
                return $this->$method();
            case 'isAjaxRequest':
            case 'isHttps':
            case 'isFlashRequest':
            case 'requestMethod':
                return $this->$offset;
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
        switch ($offset) {
            case 'moduleId':
                $this->setModuleId($value);
        }
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
     * _setUri
     *
     * @return void
     */
    private function _setUri()
    {
        $url = $this->getServer('SCRIPT_URI');
        if (!empty($url)) {
            $this->_uri = new \GuzzleHttp\Psr7\Uri($url);
        } else {
            $uri    = new \GuzzleHttp\Psr7\Uri();
            $scheme = $this->getServer('REQUEST_SCHEME');
            if (!empty($scheme)) {
                $uri = $uri->withScheme($scheme);
            } else {
                $isHttps = $this->getServer('HTTPS');
                if (!empty($isHttps) && $isHttps != 'off') {
                    $uri = $uri->withScheme('https');
                } else {
                    $uri = $uri->withScheme('http');
                }
            }
            $httpHost    = $this->getServer('HTTP_HOST');
            $httpHostArr = explode(':', $httpHost);
            if (count($httpHostArr) > 1) {
                list($host, $port) = $httpHostArr;
            } else {
                $host = $httpHost;
                $port = null;
            }
            if (empty($host)) {
                $host = $this->getServer('SERVER_NAME');
            }
            $uri = $uri->withHost($host);
            if (empty($port)) {
                $port = $this->getServer('SERVER_PORT');
            }
            if ($port != 80) {
                $uri = $uri->withPort($port);
            }
            $requestUrl = $this->getServer('REQUEST_URI');
            list($path, ) = explode('?', $requestUrl);
            if (empty($path)) {
                $path = $this->getServer('SCRIPT_NAME');
            }
            if ($path != '/') {
                $uri = $uri->withPath($path);
            }
            $this->_uri = $uri;
        }
        $queryString = $this->getServer('QUERY_STRING');
        if (empty($queryString)) {
            $query = $this->getParameterGet();
            if (!empty($query)) {
                $queryString = http_build_query($query);
            }
        }
        if (!empty($queryString)) {
            $this->_uri = $this->_uri->withQuery($queryString);
        }
    }

    /**
     * _findModuleId
     *
     * @param string $moduleId module id
     *
     * @return void
     */
    private function _findModuleId($moduleId = null)
    {
        if (!empty($moduleId)) {
            $this->_moduleId = $moduleId;
        } else {
            $this->_moduleId = $this->getParameter('m_id');
        }
    }

    /**
     * _findLanguage
     *
     * @return void
     */
    private function _findLanguage()
    {
        if (isset($this['parameter']['lang'])) {
            $this->_lang = $this['parameter']['lang'];
        } else if (isset($this['cookie']['lang'])) {
            $this->_lang = $this['cookie']['lang'];
        }
    }

    /**
     * _findCountry
     *
     * @return void
     */
    private function _findCountry()
    {
        if (isset($this['get']['cc'])) {
            $this->_country = $this['get']['cc'];
        } else if (isset($this['cookie']['cc'])) {
            $this->_country = $this['cookie']['cc'];
        }
    }

    /**
     * _findBrowser
     *
     * @return void
     */
    private function _findBrowser()
    {
        if (ini_get('browscap')) {
            $browser        = get_browser(null, true);
            $this->_browser = $browser;
        } else {
            if (filter_has_var(INPUT_SERVER, 'HTTP_USER_AGENT')) {
                $browser        = filter_input(INPUT_SERVER, 'HTTP_USER_AGENT');
                $this->_browser = $this->_matchBrowser($browser);
            }
        }
        !empty($this->_browser) or $this->_browser = array();
    }

    /**
     * _matchBrowser
     *
     * @param string $userAgent user agent
     *
     * @return array
     */
    private function _matchBrowser($userAgent)
    {
        $platform           = '';
        $browser            = '';
        $version            = '';
        $platformMatchArray = array(
            'Windows NT 6.3'     => 'Windows 8',
            'Windows NT 6.2'     => 'Windows 8.1',
            'Windows NT 6.1'     => 'Windows 7',
            'Windows NT 6.0'     => 'Windows Vista',
            'Windows NT 5.0'     => 'Windows 2000',
            'Windows NT 5'       => 'Windows XP',
            'Windows NT'         => 'Windows NT',
            'Windows 98'         => 'Windows 98',
            'Windows 95'         => 'Windows 95',
            'wds 7'              => 'Windows Phone 7',
            'wds 8'              => 'Windows Phone 8',
            'Windows Phone OS 7' => 'Windows Phone 7',
            'Windows Phone 8'    => 'Windows Phone 8',
            'Windows Phone'      => 'Windows Phone',
            'Win 9'              => 'Windows ME',
            'Win'                => 'Windows',
            'Ipad'               => 'Ipad',
            'iPhone'             => 'Iphone',
            'Mac OS'             => 'Mac OS',
            'Android'            => 'Android',
            'Linux'              => 'Linex',
            'Unix'               => 'Unix',
        );
        $browserMatchArray  = array(
            'MSIE'      => 'IE',
            'Firefox'   => 'Firefox',
            'Chrome'    => 'Chrome',
            'Safari'    => 'Safari',
            'Opera'     => 'Opera',
            'Maxthon'   => 'Maxthon',
            'UCBrowser' => 'UC',
            'Android'   => 'Android',
        );
        foreach ($platformMatchArray as $key => $value) {
            if (mb_strpos($userAgent, $key)) {
                $platform = $value;
            }
        }
        foreach ($browserMatchArray as $key => $value) {
            if (mb_strpos($userAgent, $key)) {
                $browser = $value;
                break;
            }
        }
        if (empty($version)) {
            if (preg_match("/rv:(\d+\.?\d*)/", $userAgent, $match) != 0) {
                $version = $match[1];
            } else if (preg_match("/$browser\/(\d+\.?\d*)/", $userAgent, $match) != 0) {
                $version = $match[1];
            } else if (preg_match("/$browser (\d+\.?\d*)/", $userAgent, $match) != 0) {
                $version = $match[1];
            }
        }

        if (empty($browser)) {
            if ($platform == 'Windows 8' || $platform == 'Windows 8.1' || $platform == 'Windows 7') {
                $browser = 'IE';
            }
        }

        return array(
            'platform' => $platform,
            'browser'  => $browser,
            'version'  => $version,
        );
    }

    /**
     * ._findDevice
     *
     * @return void
     */
    private function _findDevice()
    {
        $clientkeywords = array(
            'nokia',
            'sony',
            'ericsson',
            'mot',
            'samsung',
            'htc',
            'sgh',
            'lg',
            'sharp',
            'sie-',
            'philips',
            'panasonic',
            'alcatel',
            'lenovo',
            'iphone',
            'ipod',
            'blackberry',
            'meizu',
            'android',
            'netfront',
            'symbian',
            'ucweb',
            'windowsce',
            'palm',
            'operamini',
            'operamobi',
            'openwave',
            'nexusone',
            'cldc',
            'midp',
            'wap',
            'mobile',
        );
        $isDevice       = false;
        if (isset($_SERVER['HTTP_X_WAP_PROFILE'])) {
            $isDevice = true;
        } else if (isset($_SERVER['HTTP_VIA']) && mb_stristr($_SERVER['HTTP_VIA'], "wap")) {
            $isDevice = true;
        } else if (isset($this->_browser['platform']) && ($this->_browser['platform'] == 'Android' || $this->_browser['platform'] == 'Iphone' || $this->_browser['platform'] == 'Ipad' || $this->_browser['platform'] == 'Windows Phone7' || $this->_browser['platform'] == 'Windows Phone 8')
        ) {
            $isDevice = true;
        }
        $match = array();
        if (isset($_SERVER['HTTP_USER_AGENT']) && preg_match(
                        "/(" . implode('|', $clientkeywords) . ")/i",
                        mb_strtolower($_SERVER['HTTP_USER_AGENT']),
                        $match
                )) {
            $isDevice = true;
        }
        if ($isDevice) {
            $platform      = (isset($this->_browser['platform'])) ? $this->_browser['platform'] : 'unknown';
            $this->_device = array(
                'platform' => $platform,
                'device'   => ((isset($match[1])) ? $match[1] : 'unknown'),
            );
        }
    }

    /**
     * _getRequestType
     *
     * @return void
     */
    private function _getRequestType()
    {
        $this->requestMethod ?: $this->requestMethod = mb_strtoupper(isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : 'GET');
    }

    /**
     * _getIsAjaxRequest
     *
     * @return void
     */
    private function _getIsAjaxRequest()
    {
        $this->isAjaxRequest ?: $this->isAjaxRequest = (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest');
    }

    /**
     * getIsSecureConnection
     *
     * @return void
     */
    private function _getIsSecureConnection()
    {
        $this->isHttps = isset($_SERVER['HTTPS']) && ($_SERVER['HTTPS'] == 'on' || $_SERVER['HTTPS'] == 1) || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https');
    }

    /**
     * getIsFlashRequest
     *
     * @return void
     */
    private function _getIsFlashRequest()
    {
        $this->isFlashRequest = isset($_SERVER['HTTP_USER_AGENT']) && (mb_stripos($_SERVER['HTTP_USER_AGENT'], 'Shockwave') !== false || mb_stripos($_SERVER['HTTP_USER_AGENT'], 'Flash') !== false);
    }

    /**
     * setModuleId
     *
     * @param string $moduleId module id
     *
     * @return void
     */
    public function setModuleId($moduleId)
    {
        $this->_moduleId = $moduleId;
    }

    /**
     * getMethod
     *
     * @return type
     */
    public function getMethod()
    {
        $this->_method ?: $this->_method = $this['server']['REQUEST_METHOD'];
        return $this->_method;
    }

    /**
     * getRequestTime
     *
     * @return type
     */
    public function getRequestTime()
    {
        $this->_method ?: $this->_method = $this['server']['REQUEST_TIME'];
        return $this->_method;
    }

    /**
     * getRequestTimeFloat
     *
     * @return type
     */
    public function getRequestTimeFloat()
    {
        $this->_method ?: $this->_method = $this['server']['REQUEST_TIME_FLOAT'];
        return $this->_method;
    }

    /**
     * getParameter
     *
     * @param string $key    key
     * @param string $filter filter key
     * @param string $flag   flag key
     *
     * @return mixed
     */
    public function getParameter($key = null, $filter = FILTER_DEFAULT, $flag = null)
    {
        if (isset($key)) {
            if (isset($_REQUEST[$key])) {
                return filter_var($_REQUEST[$key], $filter, $flag);
            }
            return null;
        }
        return filter_var_array($_REQUEST);
    }

    /**
     * getParameterGet
     *
     * @param string $key    key
     * @param string $filter filter key
     * @param string $flag   flag key
     *
     * @return mixed
     */
    public function getParameterGet($key = null, $filter = FILTER_DEFAULT, $flag = null)
    {
        if (isset($key)) {
            if (filter_has_var(INPUT_GET, $key)) {
                return filter_input(INPUT_GET, $key, $filter, $flag);
            } else {
                return null;
            }
        }
        return filter_input_array(INPUT_GET);
    }

    /**
     * getParameterPost
     *
     * @param string $key    key
     * @param string $filter filter key
     * @param string $flag   flag key
     *
     * @return mixed
     */
    public function getParameterPost($key = null, $filter = FILTER_DEFAULT, $flag = null)
    {
        if (isset($key)) {
            if (filter_has_var(INPUT_POST, $key)) {
                return filter_input(INPUT_POST, $key, $filter, $flag);
            } else {
                return null;
            }
        }
        return filter_input_array(INPUT_POST);
    }

    /**
     * getCookie
     *
     * @param string $key    key
     * @param string $filter filter key
     * @param string $flag   flag key
     *
     * @return mixed
     */
    public function getCookie($key = null, $filter = FILTER_DEFAULT, $flag = null)
    {
        if (isset($key)) {
            if (filter_has_var(INPUT_COOKIE, $key)) {
                return filter_input(INPUT_COOKIE, $key, $filter, $flag);
            } else {
                return null;
            }
        }
        return filter_input_array(INPUT_COOKIE);
    }

    /**
     * getEnv
     *
     * @param string $key    key
     * @param string $filter filter key
     * @param string $flag   flag key
     *
     * @return mixed
     */
    public function getEnv($key = null, $filter = FILTER_DEFAULT, $flag = null)
    {
        if (isset($key)) {
            if (filter_has_var(INPUT_ENV, $key)) {
                return filter_input(INPUT_ENV, $key, $filter, $flag);
            } else {
                return null;
            }
        }
        return filter_input_array(INPUT_ENV);
    }

    /**
     * getSession
     *
     * @param string $key    key
     * @param string $filter filter key
     * @param string $flag   flag key
     *
     * @return mixed
     */
    public function getSession($key = null, $filter = FILTER_DEFAULT, $flag = null)
    {
        if (session_id() === '') {
            session_start();
        }
        if (isset($key)) {
            return (isset($_SESSION[$key])) ? filter_var($_SESSION[$key], $filter, $flag) : null;
        }
        return $_SESSION;
    }

    /**
     * getServer
     *
     * @param string $key    key
     * @param string $filter filter key
     * @param string $flag   flag key
     *
     * @return mixed
     */
    public function getServer($key = null, $filter = FILTER_DEFAULT, $flag = null)
    {
        if (isset($key)) {
            if (filter_has_var(INPUT_SERVER, $key)) {
                return filter_input(INPUT_SERVER, $key, $filter, $flag);
            } else {
                return null;
            }
        }
        return filter_input_array(INPUT_SERVER);
    }

    /**
     * getBrowser
     *
     * @return array
     */
    public function getBrowser()
    {
        if (empty($this->_browser)) {
            $this->_findBrowser();
        }
        return $this->_browser;
    }

    /**
     * getProperty
     *
     * @return string
     */
    public function getProperty()
    {
        if ($this->_moduleId) {
            $parseModule = explode('.', $this->_moduleId);
            return $parseModule[0];
        }
        return null;
    }

    /**
     * getProperty
     *
     * @return string
     */
    public function getContry()
    {
        $this->_findCountry();
        return $this->_country;
    }

    /**
     * getModuleId
     *
     * @return string
     */
    public function getModuleId()
    {
        $this->_moduleId ?: $this->_findModuleId();
        return $this->_moduleId;
    }

    /**
     * getDevice
     *
     * @return array
     */
    public function getDevice()
    {
        $this->_device ?: $this->_findDevice();
        return $this->_device;
    }

    /**
     * getLanguage
     *
     * @return string
     */
    public function getLanguage()
    {
        $this->_findLanguage();
        return $this->_lang;
    }

    /**
     * getFormatType
     *
     * @return string
     */
    public function getFormatType()
    {
        $format = $this->get['fmt'] ?? \loeye\base\RENDER_TYPE_SEGMENT;
        if (in_array($format, $this->_allowedFormatType)) {
            return $format;
        } else {
            return \loeye\base\RENDER_TYPE_SEGMENT;
        }
    }

    /**
     * getUri
     *
     * @return \GuzzleHttp\Psr7\Uri
     */
    public function getUri()
    {
        if ($this->_uri instanceof \GuzzleHttp\Psr7\Uri) {
            return $this->_uri;
        } else {
            $this->_setUri();
            return $this->_uri;
        }
    }

}
