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

use loeye\base\AutoLoadRegister;
use loeye\base\Context;
use loeye\base\Exception;
use loeye\base\Factory;
use loeye\base\Logger;
use loeye\base\Utils;
use loeye\error\ResourceException;
use loeye\lib\Cookie;
use loeye\web\Resource;
use loeye\web\Template;
use ReflectionException;
use Smarty;
use SmartyException;
use const loeye\base\RENDER_TYPE_JSON;
use const loeye\base\RENDER_TYPE_SEGMENT;
use const loeye\base\RENDER_TYPE_XML;

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
abstract class Dispatcher
{

    /**
     *
     * @var Context
     */
    protected $context;

    /**
     *
     * @var int
     */
    protected $traceCount = 0;

    /**
     * process mode
     *
     * @var int
     */
    protected $processMode;

    /**
     *
     * @var array
     */
    protected $tracedContextData;

    /**
     * @var bool
     */
    protected $isWeb = true;


    /**
     * __construct
     *
     * @param int $processMode process mode
     *
     * @return void
     */
    public function __construct($processMode = LOEYE_PROCESS_MODE__NORMAL)
    {
        $this->context = new Context();
        $this->context->setIsWeb($this->isWeb);
        $this->processMode = $processMode;
        if ($this->processMode > LOEYE_PROCESS_MODE__NORMAL) {
            $this->setTraceDataIntoContext(array());
        }
        AutoLoadRegister::initApp();
        set_error_handler(array(Utils::class, 'errorHandle'));
    }


    /**
     * getContext
     *
     * @return Context
     */
    public function getContext(): Context
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


    abstract public function dispatch($moduleId = null);


    abstract protected function initIOObject($moduleId);


    /**
     * cacheContent
     *
     * @param array $view view setting
     * @param string $content content
     *
     * @return void
     */
    protected function cacheContent($view, $content): void
    {

    }


    /**
     * getContent
     *
     * @param array $view view setting
     *
     * @return string|null
     */
    protected function getContent($view): ?string
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
    protected function getCacheId($view): ?string
    {
        return null;
    }


    /**
     * initGenericObj
     *
     * @return void
     */
    protected function initAppConfig(): void
    {

        $property = $this->context->getRequest()->getProperty();
        if (!defined('PROJECT_PROPERTY')) {
            define('PROJECT_PROPERTY', $property);
        }
        $appConfig = Factory::appConfig();
        $appConfig->setLocale($this->context->getRequest()->getLanguage());
        $this->context->setAppConfig($appConfig);
    }


    /**
     * initConfigConstants
     *
     * @return void
     */
    protected function initConfigConstants(): void
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
    protected function initLogger(): void
    {
        $logLevel = $this->context->getAppConfig()->getSetting('application.logger.level', Logger::LOEYE_LOGGER_TYPE_DEBUG);
        define('RUNTIME_LOGGER_LEVEL', $logLevel);
    }


    /**
     * setTimezone
     *
     * @return void
     */
    protected function setTimezone(): void
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
    protected function initComponent(): void
    {
        $component = $this->context->getAppConfig()->getSetting('application.component');
        if (!empty($component)) {
            foreach ((array)$component as $item => $list) {
                if ($item === 'namespace') {
                    foreach ((array)$list as $ns => $path) {
                        array_reduce((array)$path, static function ($ns, $item) {
                            AutoLoadRegister::addNamespace($ns, $item);
                            return $ns;
                        }, $ns);
                    }
                } else if ($item === 'alias') {
                    foreach ($list as $as => $path) {
                        array_reduce((array)$path, static function ($as, $item) {
                            AutoLoadRegister::addAlias($as, $item);
                            return $as;
                        }, $as);
                    }
                } else if ($item === 'folders') {
                    foreach ((array)$list as $dir) {
                        AutoLoadRegister::addDir($dir);
                    }
                } else {
                    foreach ((array)$list as $file) {
                        $ignore = false;
                        AutoLoadRegister::addFile($file, $ignore);
                    }
                }
            }
        }
        AutoLoadRegister::autoLoad();
    }


    /**
     * setTraceDataIntoContext
     *
     * @param array $pluginSetting plugin setting
     *
     * @return void
     */
    protected function setTraceDataIntoContext($pluginSetting = []): void
    {
        $trace = $this->context->getTraceData(LOEYE_CONTEXT_TRACE_KEY);
        if ($trace) {
            $time = microtime(true);
        } else {
            $time = !empty($_SERVER['REQUEST_TIME_FLOAT']) ? $_SERVER['REQUEST_TIME_FLOAT'] : microtime(true);
        }

        $trace[$this->traceCount] = array(
            'trace_time' => $time,
            'context_data' => $this->getCurrentContextData(),
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
    protected function getCurrentContextData(): array
    {
        $data = [];
        if (LOEYE_PROCESS_MODE__TRACE !== $this->processMode) {
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
    protected function redirectUrl(): void
    {
        $redirectUrl = $this->context->getResponse()->getRedirectUrl();

        if (!empty($redirectUrl)) {
            if ($this->processMode > LOEYE_PROCESS_MODE__NORMAL) {
                $this->setTraceDataIntoContext(array());
                Utils::logContextTrace($this->context);
            }
            $this->context->getResponse()->redirect($redirectUrl);
        }
    }

    /**
     * executeOutput
     *
     * @return void
     * @throws ReflectionException
     */
    protected function executeOutput(): void
    {
        $format = $this->context->getResponse()->getFormat();
        if ($format === null) {
            $format = $this->context->getFormat();
        }

        $renderObj = Factory::getRender($format);

        $renderObj->header($this->context->getResponse());
        $renderObj->output($this->context->getResponse());
    }

    /**
     * executeView
     *
     * @param array $view view setting
     *
     * @return void
     * @throws Exception
     * @throws ResourceException
     * @throws SmartyException
     */
    protected function executeView($view): void
    {
        if ($view) {
            if (in_array($this->context->getFormat(), [RENDER_TYPE_JSON, RENDER_TYPE_XML])) {
                $this->context->getResponse()->setFormat(RENDER_TYPE_SEGMENT);
            }
            $content = $this->getContent($view);
            if (!$content) {
                if (isset($view['src'])) {
                    ob_start();
                    Factory::includeView($this->context, $view);
                    $content = ob_get_clean();
                } else if (isset($view['tpl'])) {
                    $loeyeTemplate = $this->context->getTemplate();
                    if (!($loeyeTemplate instanceof Template)) {
                        $loeyeTemplate = new Template($this->context);

                        $caching = Smarty::CACHING_OFF;
                        $cacheLifeTime = 0;
                        if (isset($view['cache'])) {
                            if ($view['cache']) {
                                $caching = Smarty::CACHING_LIFETIME_CURRENT;
                                if (is_numeric($view['cache'])) {
                                    $cacheLifeTime = $view['cache'];
                                } else {
                                    $cacheLifeTime = 0;
                                }
                            } else {
                                $caching = Smarty::CACHING_OFF;
                                $cacheLifeTime = 0;
                            }
                        } else if (defined('LOEYE_TEMPLATE_CACHE') && LOEYE_TEMPLATE_CACHE) {
                            $caching = Smarty::CACHING_LIFETIME_CURRENT;
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
                    $loeyeTemplate->smarty()->registerClass('Cookie', Cookie::class);
                    $loeyeTemplate->smarty()->registerClass('Utils', Utils::class);
                    Factory::includeHandle($this->context, $view);
                    $params = array();
                    if (isset($view['data'])) {
                        $params = (array)$view['data'];
                    }
                    $errors = array();
                    if (isset($view['error'])) {
                        $errors = (array)$view['error'];
                    }
                    $loeyeTemplate->assign($params, $errors);
                    $content = $loeyeTemplate->fetch($view['tpl']);
                } else if (isset($view['body'])) {
                    $viewSetting = array('src' => $view['body']);
                    ob_start();
                    Factory::includeView($this->context, $viewSetting);
                    $content = ob_get_clean();
                }
                $this->cacheContent($view, $content);
            }
            if (isset($view['head'])) {
                $headSetting = array('src' => $view['head']);
                ob_start();
                Factory::includeView($this->context, $headSetting);
                $head = ob_get_clean();

                $this->context->getResponse()->addHtmlHead($head);
            }
            if (isset($view['layout'])) {
                ob_start();
                Factory::includeLayout($this->context, $content, $view);
                $pageContent = ob_get_clean();
                $this->context->getResponse()->addOutput($pageContent, 'view');
            } else {
                $this->context->getResponse()->addOutput($content, 'view');
            }
            if (!empty($view['head_key'])) {
                $headers = (array)$view['head_key'];
                foreach ($headers as $key) {
                    $this->context->getResponse()->addHtmlHead($this->context->get($key));
                }
            }
            if (!empty($view['content_key'])) {
                $contents = (array)$view['content_key'];
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

}
