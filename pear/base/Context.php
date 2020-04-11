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
 * @version  2018-07-23 22:44:28
 * @link     https://github.com/loeyae/loeye2.git
 */

namespace loeye\base;

use ArrayAccess;
use Generator;
use loeye\client\ParallelClientManager;
use loeye\std\Request;
use loeye\std\Response;
use loeye\web\Template;
use Psr\Cache\InvalidArgumentException;

/**
 * Context
 *
 * @author   Zhang Yi <loeyae@gmail.com>
 */
class Context implements ArrayAccess
{

    /**
     * @var array
     */
    private $_data;

    /**
     * @var array
     */
    private $_cdata;
    /**
     * @var array
     */
    private $_traceData;
    /**
     * @var array
     */
    private $_errors;
    /**
     * @var AppConfig
     */
    private $_appConfig;
    /**
     * @var Request
     */
    private $_request;
    /**
     * @var Response
     */
    private $_response;
    /**
     * @var ParallelClientManager
     */
    private $_parallelClientManager;
    /**
     * @var Router
     */
    private $_router;
    /**
     * @var UrlManager
     */
    private $_urManager;
    /**
     * @var int
     */
    private $_expire;
    /**
     * @var Template
     */
    private $_template;
    /**
     * @var ModuleDefinition
     */
    private $_mDfnObj;
    /**
     * @var Cache
     */
    private $_cache;
    /**
     * @var array
     */
    private $_object = array(
        'AppConfig',
        'Request',
        'Response',
        'Router',
    );
    /**
     * @var bool
     */
    private $_errorProcessed;

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
        $this->_traceData      = array();
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
    public function setExpire($expire): void
    {
        if ($expire !== null) {
            $this->_expire = (int)$expire;
        }
    }

    /**
     * getExpire
     *
     * @return null|int
     */
    public function getExpire(): ?int
    {
        return $this->_expire ?? null;
    }

    /**
     * cacheData
     *
     * @return void
     */
    public function cacheData(): void
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
     * @throws InvalidArgumentException
     */
    public function loadCacheData(): void
    {
        if ($this->_cache instanceof Cache) {
            $array = $this->_cache->get($this->getRequest()->getModuleId());
            if ($array) {
                foreach ($array as $key => $value) {
                    $cdata = unserialize($value, null);
                    if (($cdata instanceof ContextData) && !$cdata->isExpire()) {
                        $this->_data[$key]  = $cdata;
                        $this->_cdata[$key] = $cdata;
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
    public function offsetSet($offset, $value): void
    {
        $short = mb_substr($offset, 3);
        if ($offset === 'errors') {
            if (is_array($value)) {
                foreach ($value as $key => $error) {
                    $this->addErrors($key, $error);
                }
            } else {
                $this->addErrors(0, $value);
            }
        } else if (in_array($short, $this->_object, true)) {
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
    public function offsetExists($offset): bool
    {
        $short = mb_substr($offset, 3);
        if ($offset === 'errors') {
            return $this->hasErrors();
        }

        if (in_array($short, $this->_object, true)) {
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
        if ($offset === 'errors') {
            return $this->getErrors();
        }

        if (in_array($short, $this->_object, true)) {
            return $this->$offset();
        }

        return $this->_data[$offset] ?? null;
    }

    /**
     * offsetUnset
     *
     * @param mixed $offset offset
     *
     * @return void
     */
    public function offsetUnset($offset): void
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
        $this->_traceData             = null;
    }

    /**
     * __set
     *
     * @param string $key   key
     * @param mixed  $value value
     *
     * @return void
     */
    public function __set($key, $value): void
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
    public function __unset($key): void
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
     * @return mixed
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
    public function __isset($key): bool
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
    public function set($key, $value, $expire = 1): void
    {
        $this->_data[$key] = ContextData::init($value, $expire);
    }

    /**
     * get
     *
     * @param string $key     key
     * @param mixed  $default default
     *
     * @return mixed
     */
    public function get($key, $default = null)
    {
        if (array_key_exists($key, $this->_data)) {
            $data = $this->_data[$key];
            $value = $data();
            if ($data->isExpire()) {
                unset($this->_data[$key]);
            }
            return $value;
        }
        return $default;
    }
    
    /**
     * getWithTrace
     * 
     * @param string $key key
     * 
     * @return mixed
     */
    public function getWithTrace($key)
    {
        if (array_key_exists($key, $this->_data)) {
            $data = $this->_data[$key];
            return $data(true);
        }
        return null;
    }
    
    
    /**
     * setTraceData
     *
     * @param string $key   key
     * @param mixed  $value value
     *
     * @return void
     */
    public function setTraceData($key, $value): void
    {
        $this->_traceData[$key] = $value;
    }

    /**
     * getExpire
     *
     * @param string $key key
     * 
     * @return mixed
     */
    public function getTraceData($key)
    {
        return $this->_traceData[$key] ?? null;
    }


    /**
     * db
     *
     * @return DB
     * @throws Exception
     */
    public function db(): DB
    {
        return DB::getInstance($this->getAppConfig());
    }

    /**
     * getData
     *
     * @return array
     */
    public function getData(): array
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
     */
    public function getDataGenerator(): Generator
    {
        $g = static function ($data) {
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
    public function isExist($key): bool
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
    public function isExistKey($key): bool
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
    public function isExpire($key): bool
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
    public function isEmpty($key, $ignore = true): bool
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
    public function unsetKey($key): void
    {
        if (isset($this->_data[$key])) {
            unset($this->_data[$key]);
        }
    }

    /**
     * setAppConfig
     *
     * @param AppConfig $appConfig app config
     *
     * @return void
     */
    public function setAppConfig(AppConfig $appConfig): void
    {
        $this->_appConfig = $appConfig;
    }

    /**
     * getAppConfig
     *
     * @return AppConfig|null
     */
    public function getAppConfig(): ?AppConfig
    {
        return $this->_appConfig;
    }

    /**
     * setRequest
     *
     * @param Request $request request
     *
     * @return void
     */
    public function setRequest(Request $request): void
    {
        $this->_request = $request;
    }

    /**
     * getRequest
     *
     * @return Request|null
     */
    public function getRequest(): ?Request
    {
        return $this->_request;
    }

    /**
     * setResponse
     *
     * @param Response $response response
     *
     * @return void
     */
    public function setResponse(Response $response): void
    {
        $this->_response = $response;
    }

    /**
     * getResponse
     *
     * @return Response|null $response response
     */
    public function getResponse(): ?Response
    {
        return $this->_response;
    }

    /**
     * setParallelClientManager
     *
     * @param ParallelClientManager $clientManager
     * @return void
     */
    public function setParallelClientManager(ParallelClientManager $clientManager): void
    {
        $this->_parallelClientManager = $clientManager;
    }

    /**
     * getParallelClientMgr
     *
     * @return ParallelClientManager|null
     */
    public function getParallelClientManager(): ?ParallelClientManager
    {
        return $this->_parallelClientManager;
    }

    /**
     * setRouter
     *
     * @param Router $router router
     *
     * @return void
     */
    public function setRouter(Router $router): void
    {
        $this->_router = $router;
    }

    /**
     * getRouter
     *
     * @return Router|null
     */
    public function getRouter(): ?Router
    {
        return $this->_router;
    }

    /**
     * setUrlManager
     *
     * @param UrlManager $router router
     *
     * @return void
     */
    public function setUrlManager(UrlManager $router): void
    {
        $this->_urManager = $router;
    }

    /**
     * getUrlManager
     *
     * @return UrlManager|null
     */
    public function getUrlManager(): ?UrlManager
    {
        return $this->_urManager;
    }

    /**
     * setModule
     *
     * @param ModuleDefinition $module module definition
     *
     * @return void
     */
    public function setModule(ModuleDefinition $module): void
    {
        $this->_mDfnObj = $module;
    }

    /**
     * getModule
     *
     * @return ModuleDefinition|null
     */
    public function getModule(): ?ModuleDefinition
    {
        return $this->_mDfnObj;
    }

    /**
     * setTemplate
     *
     * @param Template $template template
     *
     * @return void
     */
    public function setTemplate(Template $template): void
    {
        $this->_template = $template;
    }

    /**
     * getTemplate
     *
     * @return Template|null
     */
    public function getTemplate(): ?Template
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
    public function addErrors($errorKey, $errorList): void
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
     * @return mixed
     */
    public function getErrors($errorKey = null)
    {
        if (isset($errorKey)) {
            return $this->_errors[$errorKey] ?? null;
        }
        return $this->_errors;
    }

    /**
     * removeErrors
     *
     * @param string $errorKey error key
     *
     * @return void
     */
    public function removeErrors($errorKey): void
    {
        if (isset($this->_errors[$errorKey])) {
            unset($this->_errors[$errorKey]);
        }
    }

    /**
     * hasErrors
     *
     * @param string $errorKey error key
     *
     * @return boolean
     */
    public function hasErrors($errorKey = null): bool
    {
        if (isset($errorKey)) {
            return !empty($this->_errors[$errorKey]);
        }
        return !empty($this->_errors);
    }

}
