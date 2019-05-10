<?php

/**
 * EsiUtil.php
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
 * Description of EsiUtil
 * 
 * @author   Zhang Yi <loeyae@gmail.com>
 */
class EsiUtil
{

    static private $_instance;
    static private $_httpsInstance;
    private $_moduleServer;
    private $_idPrefix;
    private $_isTryCatch;
    private $_queryArray = array();
    private $_isHttps    = false;

    /**
     * __construct
     *
     * @param string $moduleServer module server
     * @param bool   $isHttps      is https
     *
     * @return void
     */
    public function __construct($moduleServer = '', $isHttps = false)
    {
        $this->_isHttps = $isHttps;
        if (empty($moduleServer)) {
            if (defined('MODULE_SERVER')) {
                $moduleServer = MODULE_SERVER;
            } else {
                if (filter_has_var(INPUT_SERVER, 'HTTP_HOST')) {
                    $moduleServer = filter_input(INPUT_SERVER, 'HTTP_HOST');
                } else if (filter_has_var(INPUT_SERVER, 'SERVER_NAME')) {
                    $moduleServer = filter_input(INPUT_SERVER, 'SERVER_NAME');
                } else {
                    $moduleServer = '';
                }
            }
        }
        $this->setModuleServer($moduleServer);
        $this->setTryCatchEnable(true);
    }

    /**
     * getInstance
     *
     * @param string $moduleServer module server
     *
     * @return /LOEYE/EsiUtil
     */
    static public function getInstance($moduleServer = '')
    {
        if (!self::$_instance) {
            self::$_instance = new EsiUtil($moduleServer);
        }
        return self::$_instance;
    }

    /**
     * getHttpsInstance
     *
     * @param string $moduleServer module server
     *
     * @return /LOEYE/EsiUtil
     */
    static public function getHttpsInstance($moduleServer = '')
    {
        if (!self::$_httpsInstance) {
            self::$_httpsInstance = new EsiUtil($moduleServer, true);
        }
        return self::$_httpsInstance;
    }

    /**
     * setModuleServer
     *
     * @param string $moduleServer module server
     *
     * @return void
     */
    public function setModuleServer($moduleServer)
    {
        $this->_moduleServer = $moduleServer;
    }

    /**
     * getModuleServer
     *
     * @return string
     */
    public function getModuleServer()
    {
        return $this->_moduleServer;
    }

    /**
     * setModuleIdPrefix
     *
     * @param string $prefix prefix
     *
     * @return void
     */
    public function setModuleIdPrefix($prefix)
    {
        $this->_idPrefix = $prefix;
    }

    /**
     * getModuleIdPrefix
     *
     * @return string
     */
    public function getModuleIdPrefix()
    {
        return $this->_idPrefix;
    }

    /**
     * includeModule
     *
     * @param string $moduleId     Module id
     * @param array  $extraParam   query array
     * @param string $exceptString String
     *
     * @return void
     */
    public function includeModule(
            $moduleId,
            $extraParam = array(),
            $exceptString = ''
    )
    {
        $queryString = http_build_query($extraParam);
        $this->includeModuleWithQueryString($moduleId, $queryString, $exceptString);
    }

    /**
     * includeModuleWithModuleServer
     *
     * @param string $moduleServer Module server
     * @param string $moduleId     Module id
     * @param array  $extraParam   query string
     * @param string $exceptString String
     *
     * @return void
     */
    public function includeModuleWithModuleServer(
            $moduleServer,
            $moduleId,
            $extraParam = array(),
            $exceptString = ''
    )
    {
        $queryString   = http_build_query($extraParam);
        $includeString = $this->getEsiIncludeStringWithModuleServer(
                $moduleServer,
                $moduleId,
                $queryString,
                $exceptString
        );
        echo $includeString;
    }

    /**
     * includeModuleWithQueryString
     *
     * @param string $moduleId     Module id
     * @param string $queryString  query string
     * @param string $exceptString String
     *
     * @return void
     */
    public function includeModuleWithQueryString(
            $moduleId,
            $queryString = '',
            $exceptString = ''
    )
    {
        $includeString = $this->getEsiIncludeStringWithModuleServer(
                $this->_moduleServer,
                $moduleId,
                $queryString,
                $exceptString
        );
        echo $includeString;
    }

    /**
     * specialIncludeModule
     *
     * @param array  $specialIncludeParam Special include parameters
     * @param string $moduleId            Module id
     * @param array  $extraParam          query array
     * @param string $exceptString        String
     *
     * @return void
     */
    public function specialIncludeModule(
            $specialIncludeParam,
            $moduleId,
            $extraParam = array(),
            $exceptString = ''
    )
    {
        $queryString   = http_build_query($extraParam);
        $includeString = $this->getEsiSpecialIncludeStringWithModuleServer(
                $specialIncludeParam,
                $this->_moduleServer,
                $moduleId,
                $queryString,
                $exceptString
        );

        echo $includeString;
    }

    /**
     * specialIncludeModuleWithModuleServer
     *
     * @param array  $specialIncludeParam Special include parameters
     * @param string $moduleServer        Module server
     * @param string $moduleId            Module id
     * @param array  $extraParam          query array
     * @param string $exceptString        String
     *
     * @return void
     */
    public function specialIncludeModuleWithModuleServer(
            $specialIncludeParam,
            $moduleServer,
            $moduleId,
            $extraParam = array(),
            $exceptString = ''
    )
    {
        $queryString   = http_build_query($extraParam);
        $includeString = $this->getEsiSpecialIncludeStringWithModuleServer(
                $specialIncludeParam,
                $moduleServer,
                $moduleId,
                $queryString,
                $exceptString
        );

        echo $includeString;
    }

    /**
     * specialIncludeModuleWithQueryString
     *
     * @param array  $specialIncludeParam Special include parameters
     * @param string $moduleId            Module id
     * @param string $queryString         query string
     * @param string $exceptString        String
     *
     * @return void
     */
    public function specialIncludeModuleWithQueryString(
            $specialIncludeParam,
            $moduleId,
            $queryString = '',
            $exceptString = ''
    )
    {
        $includeString = $this->getEsiSpecialIncludeStringWithModuleServer(
                $specialIncludeParam,
                $this->_moduleServer,
                $moduleId,
                $queryString,
                $exceptString
        );
        echo $includeString;
    }

    /**
     * getEsiIncludeStringWithModuleServer
     *
     * @param string $moduleServer Module server
     * @param string $moduleId     Module id
     * @param string $queryString  query string
     * @param string $exceptString String
     *
     * @return string
     */
    public function getEsiIncludeStringWithModuleServer(
            $moduleServer,
            $moduleId,
            $queryString = '',
            $exceptString = ''
    )
    {
        $scheme        = ($this->_isHttps) ? 'https' : 'http';
        $fullModuleId  = $this->_idPrefix . $moduleId;
        $includeString = "<esi:include src=\"$scheme://$moduleServer";
        $includeString .= '/_remote/?m_id=' . $fullModuleId;

        return $this->_createEsiString(
                        $includeString, $queryString, $exceptString
        );
    }

    /**
     * getEsiSpecialIncludeStringWithModuleServer
     *
     * @param array  $specialIncludeParam Special include parameters
     * @param string $moduleServer        Module server
     * @param string $moduleId            Module id
     * @param string $queryString         query string
     * @param string $exceptString        String
     *
     * @return string
     */
    public function getEsiSpecialIncludeStringWithModuleServer(
            $specialIncludeParam,
            $moduleServer,
            $moduleId,
            $queryString = '',
            $exceptString = ''
    )
    {
        if (!isset($specialIncludeParam['handler'])) {
            throw new \Exception(
                    $moduleId . ': \'handler\' is a required esi:special-include parameter.'
            );
        }

        $scheme        = ($this->_isHttps) ? 'https' : 'http';
        $fullModuleId  = $this->_idPrefix . $moduleId;
        $includeString = "<esi:special-include ";

        foreach ($specialIncludeParam as $key => $value) {
            $includeString .= "$key=\"$value\" ";
        }

        $includeString .= "src=\"$scheme://$moduleServer/_remote/?m_id=$fullModuleId";

        return $this->_createEsiString(
                        $includeString, $queryString, $exceptString
        );
    }

    /**
     * _createEsiString
     *
     * @param string $includeString Include string
     * @param string $queryString   query string
     * @param string $exceptString  String
     *
     * @return string
     */
    private function _createEsiString($includeString, $queryString = '', $exceptString = '')
    {
        if (!empty($this->_queryArray)) {
            $includeString .= '&' . http_build_query($this->_queryArray);
        }

        if (!empty($queryString)) {
            $includeString .= '&' . $queryString;
        }
        if (filter_has_var(INPUT_GET, 'm_id')) {
            $includeString .= '&p_m_id=' . filter_input(INPUT_GET, 'm_id');
        } else if (!empty($_GET['m_id'])) {
            $includeString .= '&p_m_id=' . filter_var($_GET['m_id'], FILTER_SANITIZE_URL);
        }

        $includeString .= '"/>';

        return $this->setErrorHandleTag($includeString, $exceptString);
    }

    /**
     * Error Handling
     *
     * @param string $includeString Include string
     * @param string $exceptString  String
     *
     * @return string
     */
    public function setErrorHandleTag($includeString, $exceptString = '')
    {
        if ($this->_isTryCatch) {
            $tryCatchString = '<esi:try><esi:attempt>';
            $tryCatchString .= $includeString;
            $tryCatchString .= '</esi:attempt><esi:except>' . $exceptString;
            $tryCatchString .= '</esi:except></esi:try>';
            $includeString  = $tryCatchString;
        }
        return $includeString;
    }

    /**
     * getQueryArray
     *
     * @return array
     */
    public function getQueryArray()
    {
        return $this->_queryArray;
    }

    /**
     * setQueryArray
     *
     * @param array $query query array
     *
     * @return void
     */
    public function setQueryArray($query)
    {
        $this->_queryArray = array_merge($this->_queryArray, $query);
    }

    /**
     * setQueryString
     *
     * @param string $queryString query string
     *
     * @return void
     */
    public function setQueryString($queryString)
    {
        if (!empty($queryString)) {
            $queryArray = explode('&', $queryString);
            foreach ($queryArray as $arg) {
                if (empty($arg)) {
                    continue;
                }
                list($key, $value) = explode('=', $arg);
                $this->_queryArray[$key] = $value;
            }
        }
    }

    /**
     * addQueryParam
     *
     * @param string $key   key
     * @param string $value value
     *
     * @return void
     */
    public function addQueryParam($key, $value)
    {
        $this->_queryArray[$key] = $value;
    }

    /**
     * setTryCatchEnable
     *
     * @param bool $isEnable is enable
     *
     * @return void
     */
    public function setTryCatchEnable($isEnable = false)
    {
        $this->_isTryCatch = $isEnable;
    }

    /**
     * isTryCatchEnable
     *
     * @return bool
     */
    public function isTryCatchEnable()
    {
        return $this->_isTryCatch;
    }

}
