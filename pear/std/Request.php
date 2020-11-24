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

use ArrayAccess;
use GuzzleHttp\Psr7\Uri;
use loeye\error\LogicException;
use phpDocumentor\Reflection\Types\String_;
use Symfony\Component\HttpFoundation\ParameterBag;
use const loeye\base\RENDER_TYPE_SEGMENT;

/**
 * Request
 *
 * @author   Zhang Yi <loeyae@gmail.com>
 */
class Request extends \Symfony\Component\HttpFoundation\Request
{

    private $_lang = 'zh_CN';
    private $_country = 'cn';

    /**
     * @var Router
     */
    private $router;

    private $_moduleId;
    /**
     * @var array
     */
    private $_browser;
    private $_device;
    protected $_allowedFormatType = array();
    public $isAjaxRequest;
    public $isHttps;
    public $isFlashRequest;
    public $requestMethod;

    /**
     * __construct
     *
     * @param array $query
     * @param array $request
     * @param array $attributes
     * @param array $cookies
     * @param array $files
     * @param array $server
     * @param string|null $content
     */
    public function __construct(array $query = [], array $request = [], array $attributes = [], array $cookies = [], array $files = [], array $server = [], $content = null)
    {
        parent::__construct($query, $request, $attributes, $cookies, $files, $server, $content);
        $this->_getIsAjaxRequest();
        $this->_getIsFlashRequest();
        $this->_getIsSecureConnection();
        $this->_getRequestType();
    }

    /**
     * setRouter
     *
     * @param Router $router
     * @return Request
     */
    public function setRouter(Router $router): Request
    {
        $this->router = $router;
        return $this;
    }

    /**
     * @return Router
     */
    public function getRouter(): Router
    {
        return $this->router;
    }

    /**
     * getPathVariable
     *
     * @param null $key
     * @return array|mixed|null
     */
    public function getPathVariable($key = null)
    {
        if (null === $key) {
            return $this->router->getPathVariable();
        }
        $pathVariable = $this->router->getPathVariable();
        return $pathVariable[$key] ?? null;
    }


    /**
     * _findBrowser
     *
     * @return void
     */
    private function _findBrowser(): void
    {
        if (ini_get('browscap')) {
            $browser = get_browser(null, true);
            $this->_browser = $browser;
        } else if (filter_has_var(INPUT_SERVER, 'HTTP_USER_AGENT')) {
            $browser = filter_input(INPUT_SERVER, 'HTTP_USER_AGENT');
            $this->_browser = $this->_matchBrowser($browser);
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
    private function _matchBrowser($userAgent): array
    {
        $platform = '';
        $browser = '';
        $version = '';
        $platformMatchArray = array(
            'Windows NT 6.3' => 'Windows 8',
            'Windows NT 6.2' => 'Windows 8.1',
            'Windows NT 6.1' => 'Windows 7',
            'Windows NT 6.0' => 'Windows Vista',
            'Windows NT 5.0' => 'Windows 2000',
            'Windows NT 5' => 'Windows XP',
            'Windows NT' => 'Windows NT',
            'Windows 98' => 'Windows 98',
            'Windows 95' => 'Windows 95',
            'wds 7' => 'Windows Phone 7',
            'wds 8' => 'Windows Phone 8',
            'Windows Phone OS 7' => 'Windows Phone 7',
            'Windows Phone 8' => 'Windows Phone 8',
            'Windows Phone' => 'Windows Phone',
            'Win 9' => 'Windows ME',
            'Win' => 'Windows',
            'Ipad' => 'Ipad',
            'iPhone' => 'Iphone',
            'Mac OS' => 'Mac OS',
            'Android' => 'Android',
            'Linux' => 'Linux',
            'Unix' => 'Unix',
        );
        $browserMatchArray = array(
            'MSIE' => 'IE',
            'Firefox' => 'Firefox',
            'Chrome' => 'Chrome',
            'Safari' => 'Safari',
            'Opera' => 'Opera',
            'Maxthon' => 'Maxthon',
            'UCBrowser' => 'UC',
            'Android' => 'Android',
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
            if (preg_match("/rv:(\d+\.?\d*)/", $userAgent, $match)) {
                $version = $match[1];
            } else if (preg_match("/$browser\/(\d+\.?\d*)/", $userAgent, $match)) {
                $version = $match[1];
            } else if (preg_match("/$browser (\d+\.?\d*)/", $userAgent, $match)) {
                $version = $match[1];
            }
        }

        if (empty($browser)) {
            if ($platform === 'Windows 8' || $platform === 'Windows 8.1' || $platform === 'Windows 7') {
                $browser = 'IE';
            }
        }

        return array(
            'platform' => $platform,
            'browser' => $browser,
            'version' => $version,
        );
    }

    /**
     * ._findDevice
     *
     * @return void
     */
    private function _findDevice(): void
    {
        $clientKeywords = array(
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
        $isDevice = false;
        if (isset($_SERVER['HTTP_X_WAP_PROFILE'])) {
            $isDevice = true;
        } else if (isset($_SERVER['HTTP_VIA']) && mb_stristr($_SERVER['HTTP_VIA'], 'wap')) {
            $isDevice = true;
        } else if (isset($this->_browser['platform']) && ($this->_browser['platform'] === 'Android' ||
                $this->_browser['platform'] === 'Iphone' || $this->_browser['platform'] === 'Ipad' ||
                $this->_browser['platform'] === 'Windows Phone7' || $this->_browser['platform'] === 'Windows Phone 8')
        ) {
            $isDevice = true;
        }
        $match = array();
        if (isset($_SERVER['HTTP_USER_AGENT']) && preg_match(
                '/(' . implode('|', $clientKeywords) . ')/i',
                mb_strtolower($_SERVER['HTTP_USER_AGENT']),
                $match
            )) {
            $isDevice = true;
        }
        if ($isDevice) {
            $platform = $this->_browser['platform'] ?? 'unknown';
            $this->_device = array(
                'platform' => $platform,
                'device' => ($match[1] ?? 'unknown'),
            );
        }
    }

    /**
     * _getRequestType
     *
     * @return void
     */
    private function _getRequestType(): void
    {
        $this->requestMethod = $this->getMethod();
    }

    /**
     * _getIsAjaxRequest
     *
     * @return void
     */
    private function _getIsAjaxRequest(): void
    {
        $this->isAjaxRequest = $this->isXmlHttpRequest();
    }

    /**
     * getIsSecureConnection
     *
     * @return void
     */
    private function _getIsSecureConnection(): void
    {
        $this->isHttps = (isset($_SERVER['HTTPS']) && ($_SERVER['HTTPS'] === 'on' || $_SERVER['HTTPS'] === 1)) ||
            (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');
    }

    /**
     * getIsFlashRequest
     *
     * @return void
     */
    private function _getIsFlashRequest(): void
    {
        $userAgent = $this->headers->get('User-Agent');
        $this->isFlashRequest = (mb_stripos($userAgent, 'Shockwave') !== false || mb_stripos($userAgent, 'Flash') !== false);
    }

    /**
     * setModuleId
     *
     * @param string $moduleId module id
     *
     * @return void
     */
    public function setModuleId(string $moduleId): void
    {
        $this->_moduleId = $moduleId;
    }

    /**
     * getRequestTime
     *
     * @return mixed
     */
    public function getRequestTime()
    {
        return $this->server->get('REQUEST_TIME');
    }

    /**
     * getRequestTimeFloat
     *
     * @return mixed
     */
    public function getRequestTimeFloat()
    {
        return $this->server->get('REQUEST_TIME_FLOAT');
    }

    /**
     * getParameter
     *
     * @param string $key key
     * @param int $filter filter key
     * @param string $flag flag key
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
     * getEnv
     *
     * @param string $key key
     * @param int $filter filter key
     * @param string $flag flag key
     *
     * @return mixed
     */
    public function getEnv($key = null, $filter = FILTER_DEFAULT, $flag = null)
    {
        if (isset($key)) {
            if (filter_has_var(INPUT_ENV, $key)) {
                return filter_input(INPUT_ENV, $key, $filter, $flag);
            }

            return null;
        }
        return filter_input_array(INPUT_ENV);
    }

    /**
     * getBrowser
     *
     * @return array
     */
    public function getBrowser(): ?array
    {
        if (empty($this->_browser)) {
            $this->_findBrowser();
        }
        return $this->_browser;
    }

    /**
     * getProperty
     *
     * @return string|null
     */
    public function getProperty(): ?string
    {
        if ($this->_moduleId) {
            $parseModule = explode('.', $this->_moduleId);
            return $parseModule[0];
        }
        return null;
    }

    /**
     * getCountry
     *
     * @return string
     */
    public function getCountry(): ?string
    {
        return $this->query->get('cc') ?? $this->cookies->get('cc', $this->_country);
    }

    /**
     * getModuleId
     *
     * @return string
     */
    public function getModuleId(): ?string
    {
        return $this->_moduleId ?? $this->query->get('m_id');
    }

    /**
     * getDevice
     *
     * @return array
     */
    public function getDevice(): ?array
    {
        $this->_device ?: $this->_findDevice();
        return $this->_device;
    }

    /**
     * getLanguage
     *
     * @return string
     */
    public function getLanguage(): ?string
    {
        return $this->query->get('lang', $this->cookies->get('lang'), $this->_lang);
    }

    /**
     * getFormatType
     *
     * @return string|null
     */
    public function getFormatType(): ?string
    {
        $format = $this->query->get('fmt', RENDER_TYPE_SEGMENT);
        if (in_array($format, $this->_allowedFormatType, true)) {
            return $format;
        }

        return null;
    }

    /**
     * getUri
     *
     * @return Uri
     */
    public function getUri(): ?Uri
    {
        return new Uri($this->getRequestUri());
    }

}
