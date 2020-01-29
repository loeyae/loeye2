<?php

/**
 * Dispatcher.php
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

if (!defined('LOEYE_PROCESS_MODE__NORMAL')) {
    define('LOEYE_PROCESS_MODE__NORMAL', 0);
}

if (!defined('LOEYE_PROCESS_MODE__TEST')) {
    define('LOEYE_PROCESS_MODE__TEST', 1);
}

if (!defined('LOEYE_PROCESS_MODE__TRACE')) {
    define('LOEYE_PROCESS_MODE__TRACE', 2);
}

if (!defined('LOEYE_PROCESS_MODE__ERROR_EXIT')) {
    define('LOEYE_PROCESS_MODE__ERROR_EXIT', 9);
}

if (!defined('LOEYE_CONTEXT_TRACE_KEY')) {
    define('LOEYE_CONTEXT_TRACE_KEY', 'LOEYE_TEST_TRACE');
}

/**
 * Dispatcher
 *
 * @author   Zhang Yi <loeyae@gmail.com>
 */
abstract class Dispatcher {

    /**
     *
     * @var \loeye\base\Context
     */
    protected $context;

    /**
     *
     * @var int
     */
    protected $traceCount = 0;

    /**
     * proccess mode
     *
     * @var int
     */
    protected $proccessMode;

    /**
     *
     * @var array 
     */
    protected $tracedContextData;


    /**
     * __construct
     *
     * @param int $proccessMode proccess mode
     *
     * @return void
     */
    public function __construct($proccessMode = LOEYE_PROCESS_MODE__NORMAL)
    {
        $this->context      = new \loeye\base\Context();
        $this->proccessMode = $proccessMode;
        if ($this->proccessMode > LOEYE_PROCESS_MODE__NORMAL) {
            $this->setTraceDataIntoContext(array());
        }
        \loeye\base\AutoLoadRegister::initApp();
        set_error_handler(array('\loeye\base\Utils', 'errorHandle'));
    }


    /**
     * getContext
     *
     * @return \loeye\base\Context
     */
    public function getContext()
    {
        return $this->context;
    }


    /**
     * getContextData
     *
     * @param string $key key
     *
     * @return mixed
     */
    public function getContextData($key)
    {
        return $this->context->get($key);
    }


    abstract public function dispatche($moduleId = null);


    abstract protected function initIOObject($moduleId);


    /**
     * cacheContent
     *
     * @param array  $view    view setting
     * @param string $content content
     *
     * @return void
     */
    protected function cacheContent($view, $content)
    {
        
    }


    /**
     * getContent
     *
     * @param array $view view setting
     *
     * @return string|null
     */
    protected function getContent($view)
    {
        return null;
    }


    /**
     * getCacheId
     *
     * @param array $view view setting
     *
     * @return string|null
     */
    protected function getCacheId($view)
    {
        return null;
    }


    /**
     * initGenericObj
     *
     * @param type $moduleId
     *
     * @return void
     */
    protected function initAppConfig()
    {

        $property = $this->context->getRequest()['property'];
        if (!defined('PROJECT_PROPERTY')) {
            define('PROJECT_PROPERTY', $property);
        }
        $appConfig = new \loeye\base\AppConfig($property);
        $appConfig->setPropertyName($property);
        $appConfig->setLocale($this->context->getRequest()->getLanguage());
        $this->context->setAppConfig($appConfig);
    }


    /**
     * initConfigConstants
     *
     * @return void
     */
    protected function initConfigConstants()
    {
        $constants = $this->context->getAppConfig()->getSetting('constants', array());
        foreach ($constants as $key => $value) {
            $key = mb_strtoupper($key);
            if (!defined($key)) {
                define($key, $value);
            }
        }
    }


    /**
     * initLogLevel
     * 
     * @return void
     */
    protected function initLogger()
    {
        $logLevel = $this->context->getAppConfig()->getSetting('application.logger.level', \loeye\base\Logger::LOEYE_LOGGER_TYPE_DEBUG);
        define('RUNTIME_LOGGER_LEVEL', $logLevel);
    }


    /**
     * setTimezone
     *
     * @return void
     */
    protected function setTimezone()
    {
        $timezone = $this->context->getAppConfig()->getSetting('configuration.timezone', 'UTC');
        $this->context->getAppConfig()->setTimezone($timezone);
        date_default_timezone_set($timezone);
    }


    /**
     * initComponent
     *
     * @return void
     */
    protected function initComponent()
    {
        $component = $this->context->getAppConfig()->getSetting('application.component');
        if (!empty($component)) {
            foreach ((array) $component as $item => $list) {
                if ($item == 'namespace') {
                    foreach ((array) $list as $ns => $path) {
                        array_reduce((array) $path, function ($ns, $item) {
                            \loeye\base\AutoLoadRegister::addNamespace($ns, $item);
                            return $ns;
                        }, $ns);
                    }
                } else if ($item == 'alias') {
                    foreach ($list as $as => $path) {
                        array_reduce((array) $path, function ($as, $item) {
                            \loeye\base\AutoLoadRegister::addAlias($as, $item);
                            return $as;
                        }, $as);
                    }
                } else if ($item == 'folders') {
                    foreach ((array) $list as $dir) {
                        \loeye\base\AutoLoadRegister::addDir($dir);
                    }
                } {
                    foreach ((array) $list as $fiAutoLoadRegisterle) {
                        \loeye\base\AutoLoadRegister::addFile($dir, $ignore);
                    }
                }
            }
        }
        \loeye\base\AutoLoadRegister::autoLoad();
    }


    /**
     * setTraceDataIntoContext
     *
     * @param array $pluginSetting plugin setting
     *
     * @return void
     */
    protected function setTraceDataIntoContext($pluginSetting = [])
    {
        $trace = $this->context->getTraceData(LOEYE_CONTEXT_TRACE_KEY);
        if ($trace) {
            $time  = microtime(true);
        } else {
            $time  = !empty($_SERVER['REQUEST_TIME_FLOAT']) ? $_SERVER['REQUEST_TIME_FLOAT'] : microtime(true);
        }

        $trace[$this->traceCount] = array(
            'trace_time'     => $time,
            'context_data'   => $this->getCurrentContextData() ,
            'plugin_setting' => $pluginSetting,
        );
        $this->traceCount++;
        $this->context->setTraceData(LOEYE_CONTEXT_TRACE_KEY, $trace);
        unset($contextData);
    }

    
    /**
     * getCurrentContextData
     * 
     * @return array
     */
    protected function getCurrentContextData()
    {
        $data = [];
        if (LOEYE_PROCESS_MODE__TRACE !== $this->proccessMode) {
            return $data;
        }
        if ($this->tracedContextData) {
            foreach ($this->context->getDataGenerator() as $key => $value) {
                if (!isset($this->tracedContextData)) {
                    $data[$key] = $value;
                } else if ($this->tracedContextData[$key] !== $this->context->getWithTrace($key)) {
                    $data[$key] = $value;
                }
            }
        }
        $this->tracedContextData = $this->context->getData();
        return $data;
    }


    /**
     * redirectUrl
     *
     * @return void
     */
    protected function redirectUrl()
    {
        $redirectUrl = $this->context->getResponse()->getRedirectUrl();

        if (!empty($redirectUrl)) {
            if ($this->proccessMode > LOEYE_PROCESS_MODE__NORMAL) {
                $this->setTraceDataIntoContext(array());
                \loeye\base\Utils::logContextTrace($this->context);
            }
            $this->context->getResponse()->redirect($redirectUrl);
        }
    }


    /**
     * excuteView
     *
     * @param array $view view setting
     *
     * @return void
     */
    protected function excuteView($view)
    {
        if ($view) {
            $content = $this->getContent($view);
            if (!$content) {
                if (isset($view['src'])) {
                    ob_start();
                    \loeye\base\Factory::includeView($this->context, $view);
                    $content = ob_get_clean();
                } else if (isset($view['tpl'])) {
                    $loeyeTemplate = $this->context->getTemplate();
                    if (!($loeyeTemplate instanceof \loeye\web\Template)) {
                        $loeyeTemplate = new \loeye\web\Template($this->context);

                        $caching       = \Smarty::CACHING_OFF;
                        $cacheLifeTime = 0;
                        if (isset($view['cache'])) {
                            if ($view['cache']) {
                                $caching = \Smarty::CACHING_LIFETIME_CURRENT;
                                if (is_numeric($view['cache'])) {
                                    $cacheLifeTime = $view['cache'];
                                } else {
                                    $cacheLifeTime = 0;
                                }
                            } else {
                                $caching       = \Smarty::CACHING_OFF;
                                $cacheLifeTime = 0;
                            }
                        } else if (defined('LOEYE_TEMPLATE_CACHE') && LOEYE_TEMPLATE_CACHE) {
                            $caching = \Smarty::CACHING_LIFETIME_CURRENT;
                            if (is_numeric(LOEYE_TEMPLATE_CACHE)) {
                                $cacheLifeTime = LOEYE_TEMPLATE_CACHE;
                            }
                        }
                        $cacheId = $this->getCacheId($view);
                        $loeyeTemplate->setCache($caching);
                        $loeyeTemplate->setCacheLifeTime($cacheLifeTime);
                        $loeyeTemplate->setCacheId($cacheId);
                        $this->context->setTemplate($loeyeTemplate);
                    }
                    $loeyeTemplate->smarty()->registerClass('Cookie', '\loeye\lib\Cookie');
                    $loeyeTemplate->smarty()->registerClass('Utils', '\loeye\base\Utils');
                    \loeye\base\Factory::includeHandle($this->context, $view);
                    $params = array();
                    if (isset($view['data'])) {
                        $params = (array) $view['data'];
                    }
                    $errors = array();
                    if (isset($view['error'])) {
                        $errors = (array) $view['error'];
                    }
                    $loeyeTemplate->assign($params, $errors);
                    $content = $loeyeTemplate->fetch($view['tpl']);
                } else if (isset($view['body'])) {
                    $viewsetting = array('src' => $view['body']);
                    ob_start();
                    Factory::includeView($this->context, $viewsetting);
                    $content     = ob_get_clean();
                }
                $this->cacheContent($view, $content);
            }
            if (isset($view['head'])) {
                $headsetting = array('src' => $view['head']);
                ob_start();
                \loeye\base\Factory::includeView($this->context, $headsetting);
                $head        = ob_get_clean();

                $this->context->getResponse()->addHtmlHead($head);
            }
            if (isset($view['layout'])) {
                ob_start();
                \loeye\base\Factory::includeLayout($this->context, $content, $view);
                $pageContent = ob_get_clean();
                $this->context->getResponse()->addOutput($pageContent, 'view');
            } else {
                $this->context->getResponse()->addOutput($content, 'view');
            }
            if (!empty($view['head_key'])) {
                $headers = (array) $view['head_key'];
                foreach ($headers as $key) {
                    $this->context->getResponse()->addHtmlHead($this->context->get($key));
                }
            }
            if (!empty($view['content_key'])) {
                $contents = (array) $view['content_key'];
                foreach ($contents as $key) {
                    $this->context->getResponse()->addOutput($this->context->get($key), 'data');
                }
            }
            if (isset($view['css'])) {
                $this->_addResource(Resource::RESOURCE_TYPE_CSS, $view['css']);
            }
            if (isset($view['js'])) {
                $this->_addResource(Resource::RESOURCE_TYPE_JS, $view['js']);
            }
        }
    }


    /**
     * excuteOutput
     *
     * @return void
     */
    protected function excuteOutput()
    {
        $format = $this->context->getResponse()->getFormat();
        if ($format === null) {
            $format = $this->context->getRequest()->getFormatType();
        }

        $renderObj = \loeye\base\Factory::getRender($format);

        $renderObj->header($this->context->getResponse());
        $renderObj->output($this->context->getResponse());
    }

}
