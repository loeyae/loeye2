<?php

/**
 * Context.php
 *
 * PHP version 7
 *
 * @category PHP
 * @package  LOEYE
 * @author   Zhang Yi <loeyae@gmail.com>
 * @license  http://www.apache.org/licenses/LICENSE-2.0 Apache License
 * @version  GIT: $Id $
 * @link     URL description
 */

namespace loeye\base;

/**
 * Context
 *
 * @category PHP
 * @package  LOEYE
 * @author   Zhang Yi <loeyae@gmail.com>
 * @license  http://www.apache.org/licenses/LICENSE-2.0 Apache License
 * @link     URL description
 */
class Context implements \ArrayAccess
{

    private $_data;
    private $_cdata;
    private $_errors;
    private $_appConfig;
    private $_config;
    private $_request;
    private $_response;
    private $_parallelClientManager;
    private $_router;
    private $_expire;
    private $_template;
    private $_mDfnObj;
    private $_cache;
    private $_object = array(
        'AppConfig',
        'Request',
        'Response',
        'Router',
    );

    /**
     * __construct
     *
     * @param AppConfig $appConfig application configuration
     *
     * @return void
     */
    public function __construct(AppConfig $appConfig = null)
    {
        $this->_appConfig      = $appConfig;
        $this->_data           = array();
        $this->_cdata          = array();
        $this->_errors         = array();
        $this->_errorProcessed = false;
    }

    /**
     * setExpire
     *
     * @param int $expire expire
     *
     * @return void
     */
    public function setExpire($expire)
    {
        if ($expire !== null) {
            $this->_expire = intval($expire);
        }
    }

    /**
     * getExpire
     *
     * @return null|int
     */
    public function getExpire()
    {
        if (isset($this->_expire)) {
            return $this->_expire;
        }
        return null;
    }

    /**
     * cacheData
     *
     * @return void
     */
    public function cacheData()
    {
        if ($this->_cache instanceof Cache) {
            $g    = $this->getDataGenerator();
            $data = [];
            $time = time();
            foreach ($g as $key => $value) {
                if (!$value->isExpire($time)) {
                    $data[$key] = serialize($value);
                }
            }
            if ($data) {
                $this->_cache->set($this->getRequest()->getModuleId(), $data, 0);
            }
        }
    }

    /**
     * loadCacheData
     *
     * @return void
     */
    public function loadCacheData()
    {
        if ($this->_cache instanceof Cache) {
            $array = $this->_cache->get($this->getRequest()->getModuleId());
            if ($array) {
                $time = time();
                foreach ($array as $key => $value) {
                    $cdata = unserialize($value);
                    if ($cdata instanceof ContextData) {
                        if (!$cdata->isExpire($time)) {
                            $this->_data[$key]  = $cdata;
                            $this->_cdata[$key] = $cdata;
                        }
                    }
                }
            }
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
        $short = mb_substr($offset, 3);
        if ($offset == 'errors') {
            if (is_array($value)) {
                foreach ($value as $key => $error) {
                    $this->addErrors($key, $error);
                }
            } else {
                $this->addErrors(0, $value);
            }
        } else if (in_array($short, $this->_object)) {
            $this->$offset($value);
        } else {
            $this->_data[$offset] = ContextData::init($value);
        }
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
        $short = mb_substr($offset, 3);
        if ($offset == 'errors') {
            return $this->hasErrors();
        } else if (in_array($short, $this->_object)) {
            return true;
        }
        return isset($this->_data[$offset]);
    }

    /**
     * offsetGet
     *
     * @param mixed $offset offset
     *
     * @return  mixed
     */
    public function offsetGet($offset)
    {
        $short = mb_substr($offset, 3);
        if ($offset == 'errors') {
            return $this->getErrors();
        } else if (in_array($short, $this->_object)) {
            return $this->$offset();
        } else if (isset($this->_data[$offset])) {
            return $this->_data[$offset];
        }
        return null;
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
        if (isset($this->_data[$offset])) {
            unset($this->_data[$offset]);
        }
    }

    /**
     * __destruct
     *
     * @return void
     */
    public function __destruct()
    {
        $this->_appConfig             = null;
        $this->_data                  = null;
        $this->_errors                = null;
        $this->_parallelClientManager = null;
        $this->_request               = null;
        $this->_response              = null;
        $this->_router                = null;
        $this->_template              = null;
    }

    /**
     * __set
     *
     * @param stirng $key   key
     * @param mixed  $value value
     *
     * @return void
     */
    public function __set($key, $value)
    {
        $this->_data[$key] = ContextData::init($value);
    }

    /**
     * __unset
     *
     * @param string $key key
     *
     * @return void
     */
    public function __unset($key)
    {
        if (isset($this->_data[$key])) {
            unset($this->_data[$key]);
        }
    }

    /**
     * __get
     *
     * @param string $key key
     *
     * @return void
     */
    public function __get($key)
    {
        if (array_key_exists($key, $this->_data)) {
            return $this->_data[$key];
        }
        return null;
    }

    /**
     * __isset
     *
     * @param string $key key
     *
     * @return boolean
     */
    public function __isset($key)
    {
        return isset($this->_data[$key]);
    }

    /**
     * set
     *
     * @param string $key    key
     * @param mixed  $value  value
     * @param int    $expire int
     *
     * @return void
     */
    public function set($key, $value, $expire = null)
    {
        $this->_data[$key] = ContextData::init($value, $expire, time());
    }

    /**
     * get
     *
     * @param string $key     key
     * @param mixed  $default default
     *
     * @return type
     */
    public function get($key, $default = null)
    {
        if (array_key_exists($key, $this->_data)) {
            return $this->_data[$key]();
        }
        return $default;
    }

    /**
     * db
     * 
     * @return \loeye\base\DB
     */
    public function db()
    {
        return DB::getInstance($this->getAppConfig());
    }

    /**
     * getData
     *
     * @return array
     */
    public function getData()
    {
        $data = [];
        foreach ($this->_data as $key => $value) {
            $data[$key] = $value();
        }
        return $data;
    }

    /**
     * getDataGenerator
     *
     * @return Generator
     */
    public function getDataGenerator()
    {
        $g = function ($data) {
            foreach ($data as $key => $value) {
                yield $key => $value();
            }
        };
        return $g($this->_data);
    }

    /**
     * isExist
     *
     * @param string $key key
     *
     * @return boolean
     */
    public function isExist($key)
    {
        return isset($this->_data[$key]);
    }

    /**
     * isExistKey
     *
     * @param string $key key
     *
     * @return boolean
     */
    public function isExistKey($key)
    {
        return array_key_exists($key, $this->_data);
    }

    /**
     * isExpire
     *
     * @param string $key key
     *
     * @return boolean
     */
    public function isExpire($key)
    {
        if (isset($this->_cdata[$key])) {
            return $this->_cdata[$key]->isExpire();
        }
        return true;
    }

    /**
     * isEmpty
     *
     * @param string $key    key
     * @param bool   $ignore ignore
     *
     * @return boolean
     */
    public function isEmpty($key, $ignore = true)
    {
        if (!isset($this->_data[$key])) {
            return true;
        }
        return $this->_data[$key]->isEmpyt($ignore);
    }

    /**
     * unsetKey
     *
     * @param string $key key
     *
     * @return void
     */
    public function unsetKey($key)
    {
        if (isset($this->_data[$key])) {
            unset($this->_data[$key]);
        }
    }

    /**
     * setAppConfig
     *
     * @param \loeye\base\AppConfig $appConfig app config
     *
     * @return void
     */
    public function setAppConfig(AppConfig $appConfig)
    {
        $this->_appConfig = $appConfig;
    }

    /**
     * getAppConfig
     *
     * @return \loeye\base\AppConfig
     */
    public function getAppConfig()
    {
        return $this->_appConfig;
    }

    /**
     *  setConfig
     *
     * @param \loeye\base\Configuration $config
     *
     * @return void
     */
    public function setConfig(Configuration $config)
    {
        $this->_config = $config;
    }

    /**
     * getConfig
     *
     * @return \loeye\base\Configuration $config
     */
    public function getConfig()
    {
        return $this->_config;
    }

    /**
     * setRequest
     *
     * @param \loeye\std\Request $request request
     *
     * @return void
     */
    public function setRequest(\loeye\std\Request $request)
    {
        $this->_request = $request;
    }

    /**
     * getRequest
     *
     * @return \loeye\std\Request
     */
    public function getRequest()
    {
        return $this->_request;
    }

    /**
     * setResponse
     *
     * @param \loeye\std\Response $response response
     *
     * @return void
     */
    public function setResponse(\loeye\std\Response $response)
    {
        $this->_response = $response;
    }

    /**
     * getResponse
     *
     * @return \loeye\std\Response $response response
     */
    public function getResponse()
    {
        return $this->_response;
    }

    /**
     * setParallelClientManager
     *
     * @param  \loeye\client\ParallelClientManager $clientMgr client mgr
     *
     * @return void
     */
    public function setParallelClientManager(\loeye\client\ParallelClientManager $clientManager)
    {
        $this->_parallelClientManager = $clientManager;
    }

    /**
     * getParallelClientMgr
     *
     * @return \loeye\client\ParallelClientManager
     */
    public function getParallelClientManager()
    {
        return $this->_parallelClientManager;
    }

    /**
     * setRouter
     *
     * @param \loeye\base\Router $router router
     *
     * @return void
     */
    public function setRouter(\loeye\base\Router $router)
    {
        $this->_router = $router;
    }

    /**
     * getRouter
     *
     * @return \loeye\base\Router
     */
    public function getRouter()
    {
        return $this->_router;
    }

    /**
     * setUrlManager
     *
     * @param \loeye\base\UrlManager $router router
     *
     * @return void
     */
    public function setUrlManager(UrlManager $router)
    {
        $this->_router = $router;
    }

    /**
     * getUrlManager
     *
     * @return \loeye\base\UrlManager
     */
    public function getUrlManager()
    {
        return $this->_router;
    }

    /**
     * setModule
     *
     * @param \loeye\base\ModuleDefinition $module module definition
     *
     * @return void
     */
    public function setModule(ModuleDefinition $module)
    {
        $this->_mDfnObj = $module;
    }

    /**
     * getModule
     *
     * @return \loeye\base\ModuleDefinition
     */
    public function getModule()
    {
        return $this->_mDfnObj;
    }

    /**
     * setTemplate
     *
     * @param \loeye\web\Template $template template
     *
     * @return void
     */
    public function setTemplate(\loeye\web\Template $template)
    {
        $this->_template = $template;
    }

    /**
     * getTemplate
     *
     * @return \loeye\web\Template
     */
    public function getTemplate()
    {
        return $this->_template;
    }

    /**
     * addErrors
     *
     * @param string $errorKey  error key
     * @param mixed  $errorList error list
     *
     * @return void
     */
    public function addErrors($errorKey, $errorList)
    {
        if (!is_array($errorList)) {
            $errorList = array($errorList);
        }
        if (isset($this->_errors[$errorKey])) {
            $this->_errors[$errorKey] = array_merge($this->_errors[$errorKey], $errorList);
        } else {
            $this->_errors[$errorKey] = $errorList;
        }
    }

    /**
     * getErrors
     *
     * @param string $errorKey error key
     *
     * @return array
     */
    public function getErrors($errorKey = null)
    {
        if (isset($errorKey)) {
            return isset($this->_errors[$errorKey]) ? $this->_errors[$errorKey] : null;
        }
        return $this->_errors;
    }

    /**
     * removeErrors
     *
     * @param string $errorkey error key
     *
     * @return void
     */
    public function removeErrors($errorkey)
    {
        if (isset($this->_errors[$errorkey])) {
            unset($this->_errors[$errorkey]);
        }
    }

    /**
     * hasErrors
     *
     * @param string $errorKey error key
     *
     * @return boolean
     */
    public function hasErrors($errorKey = null)
    {
        if (isset($errorKey)) {
            return !empty($this->_errors[$errorKey]);
        }
        return !empty($this->_errors);
    }

}
