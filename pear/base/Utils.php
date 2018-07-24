<?php

/**
 * Utils.php
 *
 * PHP version 7
 *
 * Licensed under the Apache License, Version 2.0 (the "License"),
 * see LICENSE for more details: http://www.apache.org/licenses/LICENSE-2.0.
 *
 * @category PHP
 * @package  LOEYE
 * @author   Zhang Yi <loeyae@gmail.com>
 * @version  GIT: $Id: Zhang Yi $
 * @link     https://github.com/loeyae/loeye.git
 */

namespace loeye\base;

/**
 * Description of Utils
 *
 * @author   Zhang Yi <loeyae@gmail.com>
 */
class Utils
{

    /**
     * addParallelClient
     *
     * @param \loeye\std\Client $client  client
     * @param \loeye\base\Context    $context context
     *
     * @return void
     */
    static public function addParallelClient(\loeye\std\Client $client, Context $context)
    {
        $context->getParallelClientMgr()->addClient($client);
    }

    /**
     * dateFormat
     *
     * @param string $locale   locale
     * @param int    $time     time
     * @param string $timezone timezone
     * @param int    $datetype datetype
     * @param int    $timetype timetype
     * @param string $pattern  pattern
     *
     * @return string
     */
    static public function dateFormat(
            $locale, $time, $timezone = 'Asia/Shanghai',
            $datetype = \IntlDateFormatter::LONG, $timetype = \IntlDateFormatter::MEDIUM,
            $pattern = 'yyyy-MM-dd HH:mm:ss'
    )
    {
        $calendar = \IntlDateFormatter::GREGORIAN;
        $dfmt     = datefmt_create($locale, $datetype, $timetype, $timezone, $calendar, $pattern);
        return datefmt_format($dfmt, $time);
    }

    /**
     * checkValue
     *
     * @param mixed  $data  data
     * @param mixed  $value value
     * @param string $key   key
     *
     * @return mixed
     */
    static public function checkValue($data, $value, $key = null)
    {
        if (empty($key)) {
            if ($data !== $value) {
                self::throwException("${data}不等于${value}", Exception::DEFAULT_LOGIC_ERROR);
            }
            return $data;
        } else if ($data instanceof Context) {
            self::checkKeyExist($data, $key);
            $origin = $data->get($key);
            if ($origin !== $value) {
                self::throwException("context的${key}不等于${value}", Exception::DEFAULT_LOGIC_ERROR);
            }
            return $origin;
        } else if (is_array($data)) {
            self::checkKeyExist($data, $key);
            $origin = $data[$key];
            if ($origin !== $value) {
                self::throwException("数组的${key}不等于${value}", Exception::DEFAULT_LOGIC_ERROR);
            }
            return $origin;
        } else {
            if ($data !== $value) {
                self::throwException(
                        print_r($data, true) . '不等于' . $value, Exception::DEFAULT_LOGIC_ERROR);
            }
            return $data;
        }
    }

    /**
     * ckeckExist
     *
     * @param \LOEYE\Context|array $data data
     * @param string                    $key  key
     *
     * @return mixed
     */
    static public function ckeckExist($data, $key)
    {
        if ($data instanceof Context) {
            if (!$data->isExist($key)) {
                self::throwException("context不存在key:${key}的值", Exception::CONTEXT_KEY_NOT_FOUND);
            }
            return $data->get($key);
        } else if (is_array($data)) {
            if (!isset($data[$key])) {
                self::throwException("数组不存在key:${key}的值", Exception::DATA_KEY_NOT_FOUND);
            }
            return $data[$key];
        } else {
            self::throwException("不存在key:${key}的值", Exception::DATA_KEY_NOT_FOUND);
        }
    }

    /**
     * checkKeyExist
     *
     * @param \LOEYE\Context|array $data data
     * @param string                    $key  key
     *
     * @return mixed
     */
    static public function checkKeyExist($data, $key)
    {
        if ($data instanceof Context) {
            if (!$data->isExistKey($key)) {
                self::throwException("context不存在key:${key}", Exception::CONTEXT_KEY_NOT_FOUND);
            }
            return $data->get($key);
        } else if (is_array($data)) {
            if (!isset($data[$key])) {
                self::throwException("数组不存在key:${key}", Exception::DATA_KEY_NOT_FOUND);
            }
            return $data[$key];
        } else {
            self::throwException("不存在key:${key}", Exception::DATA_KEY_NOT_FOUND);
        }
    }

    /**
     * checkNotEmpty
     *
     * @param \LOEYE\Context|array $data   data
     * @param string                    $key    key
     * @param boolean                   $ignore ingore 0
     *
     * @return mixed
     */
    static public function checkNotEmpty($data, $key, $ignore = true)
    {
        if ($data instanceof Context) {
            if ($data->isEmpty($key, $ignore)) {
                self::throwException("context中key:${key}为空", Exception::CONTEXT_VALUE_IS_EMPTY);
            }
            return $data->get($key);
        } else if (is_array($data)) {
            if (!isset($data[$key])) {
                self::throwException("数组中key:${key}为空", Exception::DATA_VALUE_IS_EMPTY);
            }
            if ($ignore) {
                if (empty($data[$key]) && !is_numeric($data[$key])) {
                    self::throwException("数组中key:${key}为空", Exception::DATA_VALUE_IS_EMPTY);
                }
            } else {
                if (empty($data[$key])) {
                    self::throwException("数组中key:${key}为空", Exception::DATA_VALUE_IS_EMPTY);
                }
            }
            return $data[$key];
        } else {
            self::throwException("key:${key}为空", Exception::DATA_VALUE_IS_EMPTY);
        }
    }

    /**
     * addErrors
     *
     * @param mixed               $errors  errors
     * @param \LOEYE\Context $context context
     * @param array               $setting setting
     * @param string              $default default
     *
     * @return void
     */
    static public function addErrors($errors, Context $context, $setting, $default = null)
    {
        if (empty($errors)) {
            return;
        }
        $key = null;
        if (!empty($setting['error'])) {
            $key = $setting['error'];
        } else if (!empty($setting['error_key'])) {
            $key = $setting['error_key'];
        } else if (!empty($default)) {
            $key = $default;
        } else {
            self::throwException('未设置塞入错误的key', Exception::INVALID_PARAMETER_CODE);
        }
        $context->addErrors($key, $errors);
    }

    /**
     * getErrors
     *
     * @param \LOEYE\Context $context context
     * @param array               $setting setting
     * @param string              $default default key
     *
     * @return mixed
     */
    static public function getErrors(Context $context, $setting, $default = null)
    {
        $key = null;
        if (!empty($setting['err'])) {
            $key = $setting['err'];
        } else if (!empty($setting['err_key'])) {
            $key = $setting['err_key'];
        } else if (!empty($default)) {
            $key = $default;
        } else {
            self::throwException('未设置获取错误的key', Exception::INVALID_PARAMETER_CODE);
        }
        return $context->getErrors($key);
    }

    /**
     * removeErrors
     *
     * @param \LOEYE\Context $context context
     * @param array               $setting setting
     * @param string              $default default key
     *
     * @return mixed
     */
    static public function removeErrors(Context $context, $setting, $default = null)
    {
        $key = null;
        if (!empty($setting['err'])) {
            $key = $setting['err'];
        } else if (!empty($setting['err_key'])) {
            $key = $setting['err_key'];
        } else if (!empty($default)) {
            $key = $default;
        } else {
            self::throwException('未设置移除错误的key', Exception::INVALID_PARAMETER_CODE);
        }
        return $context->removeErrors($key);
    }

    /**
     * setContextData
     *
     * @param mixed               $data    data
     * @param \loeye\base\Context $context context
     * @param array               $setting setting
     * @param string              $default default
     *
     * @return void
     */
    static public function setContextData($data, Context $context, $setting, $default = null)
    {
        if (is_null($data)) {
            return;
        }
        $key = null;
        if (!empty($setting['out'])) {
            $key = $setting['out'];
        } else if (!empty($setting['output'])) {
            $key = $setting['output'];
        } else if (!empty($setting['output_key'])) {
            $key = $setting['output_key'];
        } else if (!empty($default)) {
            $key = $default;
        } else {
            self::throwException('未设置塞入数据的key', Exception::INVALID_PARAMETER_CODE);
        }
        if (isset($setting['expire'])) {
            $expire = $setting['expire'];
        } else {
            $expire = $context->getExpire();
        }
        $context->set($key, $data, $expire);
    }

    /**
     * checkContenxtCacheData
     *
     * @param \loeye\base\Context $context context
     * @param array               $setting setting
     * @param string              $default default
     *
     * @return boolean
     */
    static public function checkContenxtCacheData(Context $context, $setting, $default = null)
    {
        $key = null;
        if (!empty($setting['out'])) {
            $key = $setting['out'];
        } else if (!empty($setting['output'])) {
            $key = $setting['output'];
        } else if (!empty($setting['output_key'])) {
            $key = $setting['output_key'];
        } else if (!empty($default)) {
            $key = $default;
        } else {
            return false;
        }
        if ($context->isExpire($key)) {
            return false;
        }
        return true;
    }

    /**
     * unsetContextData
     *
     * @param \loeye\base\Context $context context
     * @param array               $setting setting
     * @param string              $default default
     *
     * @return mixed
     */
    static public function unsetContextData(Context $context, $setting, $default = null)
    {
        $key = null;
        if (!empty($setting['in'])) {
            $key = $setting['in'];
        } else if (!empty($setting['input'])) {
            $key = $setting['input'];
        } else if (!empty($setting['input_key'])) {
            $key = $setting['input_key'];
        } else if (!empty($default)) {
            $key = $default;
        } else {
            self::throwException('未设置获取数据的key', Exception::INVALID_PARAMETER_CODE);
        }
        return $context->unsetKey($key);
    }

    /**
     * getContextData
     *
     * @param \loeye\base\Context $context context
     * @param array               $setting setting
     * @param string              $default default
     *
     * @return mixed
     */
    static public function getContextData(Context $context, $setting, $default = null)
    {
        $key = null;
        if (!empty($setting['in'])) {
            $key = $setting['in'];
        } else if (!empty($setting['input'])) {
            $key = $setting['input'];
        } else if (!empty($setting['input_key'])) {
            $key = $setting['input_key'];
        } else if (!empty($default)) {
            $key = $default;
        } else {
            self::throwException("未设置获取数据的key", Exception::INVALID_PARAMETER_CODE);
        }
        return $context->get($key);
    }

    /**
     * checkNotEmptyContextData
     *
     * @param \loeye\base\Context $context context
     * @param array               $setting setting
     * @param string              $default default
     * @param bool                $ignore  is ignore 0
     *
     * @return mixed
     */
    static public function checkNotEmptyContextData(Context $context, $setting, $default = null, $ignore = true)
    {
        $key = null;
        if (!empty($setting['in'])) {
            $key = $setting['in'];
        } else if (!empty($setting['input'])) {
            $key = $setting['input'];
        } else if (!empty($setting['input_key'])) {
            $key = $setting['input_key'];
        } else if (!empty($default)) {
            $key = $default;
        } else {
            self::throwException('未设置获取数据的key', Exception::INVALID_PARAMETER_CODE);
        }
        return self::checkNotEmpty($context, $key, $ignore);
    }

    /**
     * getData
     *
     * @param mixed $data    data
     * @param type  $key     key
     * @param type  $default default
     *
     * @return mixed
     */
    static public function getData($data, $key, $default = null)
    {
        if ($data instanceof Context) {
            return $data->get($key, $default);
        } else if (is_array($data)) {
            if (isset($data[$key])) {
                return $data[$key];
            }
        }
        return $default;
    }

    /**
     * keyFilter
     *
     * @param \loeye\base\Conext|array $data     data
     * @param array                    $required required keys
     * @param array                    $options  options keys
     * @param array                    $least    least one exist keys
     * @param boolean                  $ignore   ingore 0
     *
     * @return array
     */
    static public function keyFilter(
            $data, array $required = array(), array $options = array(), array $least = array(), $ignore = true
    )
    {
        $result = array();
        if (empty($data) || !is_array($data)) {
            return $result;
        }
        if (!empty($required)) {
            foreach ($required as $key) {
                $result[$key] = self::checkNotEmpty($data, $key, $ignore);
            }
        }
        if (!empty($options)) {
            foreach ($options as $key) {
                if (isset($data[$key])) {
                    $result[$key] = $data[$key];
                }
            }
        }
        if (!empty($least)) {
            $pattern = array_combine($least, $least);
            $keyList = array_intersect_key($data, $pattern);
            if (empty($keyList)) {
                self::throwException(
                        '数组中应至少包含如下key:' . print_r($least, true) . '中的一个',
                        Exception::DEFAULT_LOGIC_ERROR
                );
            }
            foreach ($keyList as $key => $value) {
                $result[$key] = $value;
            }
        }
        return $result;
    }

    /**
     * filterResult
     *
     * @param mixed                 $result result
     * @param mixed                 &$data  data
     * @param \loeye\abse\Exception &$error error
     *
     * @return void
     */
    static public function filterResult($result, &$data, &$error)
    {
        if ($result instanceof \Exception) {
            $error = $result;
        } else {
            $data = $result;
        }
    }

    /**
     * filterResultArray
     *
     * @param array $result  result
     * @param array &$data   data
     * @param array &$errors errors
     *
     * @return void
     */
    static public function filterResultArray($result, &$data, &$errors)
    {
        $data   = array();
        $errors = array();
        if ($result instanceof \Exception) {
            $errors = $result;
        } else if (is_array($result)) {
            foreach ($result as $key => $value) {
                if ($value instanceof \Exception) {
                    $errors[] = $value;
                } else {
                    $data[$key] = $value;
                }
            }
        } else {
            self::filterResult($result, $data, $errors);
        }
    }

    /**
     * hasException
     *
     * @param mixed $result result
     *
     * @return boole
     */
    static public function hasException($result)
    {
        return $result instanceof \Exception;
    }

    /**
     * throwException
     *
     * @param string $errorMsg  error message
     * @param int    $errorCode error code
     *
     * @return void
     * @throws Exception
     */
    static public function throwException($errorMsg, $errorCode = 500)
    {
        $logMsg = "[logic] ${errorMsg}";
        self::log($logMsg, Logger::LOEYE_LOGGER_TYPE_ERROR);
        throw new Exception($errorMsg, $errorCode);
    }

    /**
     * throwError
     *
     * @param mixed $error error
     *
     * @return void
     * @throws Exception
     */
    static public function throwError($error)
    {
        if (self::hasException($error)) {
            throw $error;
        } else {
            self::throwException(serialize($error));
        }
    }

    /**
     * mbUcfirst
     *
     * @param string $string string
     *
     * @return string
     */
    static public function mbUcfirst($string)
    {
        $ucFirstChar = mb_strtoupper(mb_substr($string, 0, 1));
        return ($ucFirstChar . mb_substr($string, 1));
    }

    /**
     * includeView
     *
     * @param string $file      file
     * @param array  $parameter parameter
     *
     * @return void
     */
    static public function includeView($file, array $parameter = array())
    {
        if (!is_file($file)) {
            $dno  = mb_strrpos($file, ".");
            $file = PROJECT_VIEWS_DIR . '/'
                    . strtr(mb_substr($file, 0, $dno), ".", "/") . mb_substr($file, $dno);
        }
        if (is_array($parameter) && !empty($parameter)) {
            extract($parameter);
        }
        include $file;
    }

    /**
     * setPageCache
     *
     * @param \loeye\base\AppConfig $appConfig appConfig
     * @param string                $moduleId  module id
     * @param string                $page      page source
     * @param int                   $expire    expire
     * @param array                 $params    params
     *
     * @return void
     */
    static public function setPageCache(AppConfig $appConfig, $moduleId, $page, $expire = 0, $params = [])
    {
        $fileKey = $moduleId;
        if (!empty($params)) {
            sort($params);
            $fileKey .= '?' . http_build_query($params);
        }
        $cache = Cache::getInstance($appConfig, 'templates');
        $cache->set('source', $page, $expire);
    }

    /**
     * getPageCache
     *
     * @param \loeye\base\AppConfig $appConfig appConfig
     * @param string                $moduleId  module id
     * @param array                 $params    params
     *
     * @return void
     */
    static public function getPageCache(AppConfig $appConfig, $moduleId, $params = [])
    {
        $fileKey = $moduleId;
        if (!empty($params)) {
            sort($params);
            $fileKey .= '?' . http_build_query($params);
        }
        $cache = Cache::getInstance($appConfig, 'templates');
        return $cache->get($key);
    }

    /**
     * includeTpl
     *
     * @param \LOEYE\Context $context context
     * @param string              $file    file name
     *
     * @return string
     */
    static public function includeTpl(Context $context, $file)
    {
        if (!is_file($file)) {
            $dno  = mb_strrpos($file, ".");
            $file = PROJECT_VIEWS_BASE_DIR . '/'
                    . strtr(mb_substr($file, 0, $dno), ".", "/") . mb_substr($file, $dno);
        }
        $template = $context->getTemplate();
        if (!($template instanceof Template)) {
            include $file;
        } else {
            return $template->fetch($file);
        }
    }

    /**
     * includeModule
     *
     * @param string $moduleId  module id
     * @param array  $parameter parameter
     *
     * @return void
     */
    static public function includeModule($moduleId, $parameter = array())
    {
        if (!empty($parameter)) {
            foreach ((array) $parameter as $key => $value) {
                if (is_numeric($key)) {
                    continue;
                }
                $_REQUEST[$key] = $value;
            }
        }
        $dispatcher = new \loeye\web\Dispatcher();
        $dispatcher->dispatche($moduleId);
    }

    /**
     * usc2ToUtf8
     *
     * @param string $string string
     *
     * @return string
     */
    static public function usc2ToUtf8($string)
    {
        return preg_replace_callback("#\\\u([0-9a-f]{4})#i", function ($matches) {
            return iconv('UCS-2', 'UTF-8', pack('H4', $matches[1]));
        }, $string);
    }

    /**
     * asciiToUtf8
     *
     * @param string $string string
     *
     * @return string
     */
    static public function asciiToUtf8($string)
    {
        $matches = array();
        $offset  = 0;
        $decode  = '';
        $string  = mb_strtolower($string);
        if (mb_strpos($string, 'x') !== false) {
            while (preg_match('#x[0-f]{2}#', $string, $matches, PREG_OFFSET_CAPTURE, $offset)) {
                $code   = $matches[0][0];
                $decode .= chr('0' . $code);
                $offset = $matches[0][1] + mb_strlen($code);
            }
        } else {
            while (preg_match('#[0-9]{2}#', $string, $matches, PREG_OFFSET_CAPTURE, $offset)) {
                $code   = $matches[0][0];
                $decode .= chr($code);
                $offset = $matches[0][1] + mb_strlen($code);
            }
        }
        return $decode;
    }

    /**
     * getArrayLevel
     *
     * @param array $array array
     *
     * @return int
     */
    static public function getArrayLevel($array)
    {
        if (is_array($array)) {
            foreach ($array as $val) {
                $level = 1;
                if (is_array($val)) {
                    $level += self::getArrayLevel($val);
                }
                $arr[] = $level;
            }
            return max($arr);
        }
        return 0;
    }

    /**
     * callUserFuncArray
     *
     * @param mixed $data    data
     * @param mixed $setting setting
     *
     * @return mixed
     */
    static public function callUserFuncArray($data, $setting)
    {
        try {
            if (isset($setting['callback'])) {
                $callback = $setting['callback'];
                unset($setting['callback']);
            } else if (isset($setting['class'])) {
                $function = self::checkKeyExist($setting, 'method');
                if (isset($setting['src'])) {
                    AutoLoadRegister::loadAlias($setting['src']);
                }
                $object   = new $setting['class'];
                $callback = array(
                    $object,
                    $function,
                );
                unset($setting['class']);
                unset($setting['src']);
                unset($setting['method']);
            } else {
                $callback = $setting;
            }
            $parameterArr = array($data);
            if (isset($setting['param'])) {
                $parameter = (array) $setting['param'];
            } else {
                $parameter = (array) $setting;
            }
            $result = call_user_func_array($callback, array_merge($parameterArr, $parameter));
            return $result;
        } catch (\Exception $e) {
            self::errorLog($e);
            self::throwException(
                    '无效的参数设定', LOEYE\Exception::INVALID_PARAMETER_CODE);
        }
    }

    /**
     * log
     *
     * @param string $message     message
     * @param int    $messageType message type
     * @param array  $trace       trace info
     *
     * @return void
     */
    static public function log($message, $messageType = Logger::LOEYE_LOGGER_TYPE_NOTICE, $trace = [])
    {
        if ($messageType == Logger::LOEYE_LOGGER_TYPE_CONTEXT_TRACE) {
            $logfile     = RUNTIME_LOG_DIR . DIRECTORY_SEPARATOR
                    . PROJECT_NAMESPACE . DIRECTORY_SEPARATOR . "trace.log";
            $messageType = Logger::LOEYE_LOGGER_TYPE_DEBUG;
        } else {
            $logfile = RUNTIME_LOG_DIR . DIRECTORY_SEPARATOR
                    . PROJECT_NAMESPACE . DIRECTORY_SEPARATOR . "error.log";
        }
        $message .= PHP_EOL;
        if (empty($trace)) {
            $traceInfo = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
            foreach ($traceInfo as $i => $t) {
                if (!isset($t['file'])) {
                    $t['file'] = 'unknown';
                }
                if (!isset($t['line'])) {
                    $t['line'] = 0;
                }
                if ($t['file'] != __FILE__ && empty($trace)) {
                    $trace = $t;
                }
                if (!isset($t['function'])) {
                    $t['function'] = 'unknown';
                }
                $message .= "#$i {$t['file']}({$t['line']}): ";
                if (isset($t['class'])) {
                    $message .= "{$t['class']}->";
                }
                $message .= "{$t['function']}()" . '\r\n';
            }
            if (isset($_SERVER['REQUEST_URI'])) {
                $message .= "# REQUEST_URI: ${_SERVER['REQUEST_URI']}" . '\r\n';
            }
        }
        Logger::log($message, $messageType, $logfile);
    }

    /**
     * errorLog
     *
     * @param \Exception $exc exception
     *
     * @return void
     */
    static public function errorLog(\Exception $exc)
    {
        $file = $exc->getFile();
        if ($file == __FILE__) {
            return;
        }
        $line    = $exc->getLine();
        $message = $exc->getMessage();
        $code    = $exc->getCode();
        if ($exc instanceof Exception) {
            $message = "[system] ${message}";
        } else {
            $message = "[other] ${message}";
        }
        Logger::trace($message, $code, $file, $line, Logger::LOEYE_LOGGER_TYPE_ERROR);
    }

    /**
     * errorHandle
     *
     * @param int    $no      no
     * @param string $message message
     * @param string $file    file
     * @param int    $line    line
     *
     * @return void
     */
    static public function errorHandle($no, $message, $file, $line)
    {
        Logger::handle($no, $message, $file, $line);
    }

    /**
     * setTraceDataIntoContext
     *
     * @param \loeye\base\Context $context       context
     * @param array               $pluginSetting plugin setting
     * @param string              $traceKey      trace key
     *
     * @staticvar int            $traceCount   trace count
     *
     * @return void
     */
    static function setTraceDataIntoContext(Context $context, $pluginSetting = array(), $traceKey = null)
    {
        static $traceCount = 0;

        if (!$traceKey) {
            $traceKey = LOEYE_CONTEXT_TRACE_KEY;
        }
        $contextData = $context->getData();
        if (isset($contextData[$traceKey])) {
            $trace = $contextData[$traceKey];
        } else {
            $trace = array();
        }
        if (empty($pluginSetting)) {
            $trace[$traceCount] = array(
                'trace_time'   => microtime(true),
                'context_data' => $contextData,
            );
        } else {
            $trace[$traceCount] = array(
                'trace_time'     => microtime(true),
                'context_data'   => $contextData,
                'plugin_setting' => $pluginSetting,
            );
        }
        $traceCount++;
        $context->set($traceKey, $trace);
    }

    /**
     * logContextTrace
     *
     * @param \loeye\base\Context $context    context
     * @param string              $traceKey   trace key
     * @param bool                $ignoreData ignore data
     *
     * @return void
     */
    static public function logContextTrace(Context $context, $traceKey = null, $ignoreData = true)
    {
        if (!$traceKey) {
            $traceKey = LOEYE_CONTEXT_TRACE_KEY;
        }
        $tracInfo = $context->get($traceKey);
        if (empty($tracInfo)) {
            return;
        }
        $startInfo = array_shift($tracInfo);
        $message   = "context trace info:" . '\r\n';
        $message   .= "# ${traceKey} init" . '\r\n';
        $message   .= "#  time: ${startInfo['trace_time']}" . '\r\n';
        if (count($tracInfo) > 1 && empty(current($tracInfo)['plugin_setting'])) {
            $pluginStartInfo = array_shift($tracInfo);
            $message         .= "# start " . '\r\n';
            $message         .= "#  time${pluginStartInfo['trace_time']}" . '\r\n';
            $t               = $pluginStartInfo['trace_time'] - $startInfo['trace_time'];
            $message         .= "#  consuming: $t" . '\r\n';
            if ($ignoreData == false) {
                $m       = serialize($pluginStartInfo['context_data']);
                $message .= "#  context data: ${m}" . '\r\n';
            }
            $prevtime = $pluginStartInfo['trace_time'];
        } else {
            $prevtime = $startInfo['trace_time'];
        }
        $endInfo = end($tracInfo);
        if (!isset($endInfo['plugin_setting'])) {
            array_pop($tracInfo);
        }
        reset($tracInfo);
        foreach ($tracInfo as $trace) {
            if (isset($trace['plugin_setting']) && isset($trace['plugin_setting']['name'])) {
                $p       = $trace['plugin_setting']['name'];
                $message .= "# ${p}" . '\r\n';
            } else {
                $message .= "# ${traceKey} process " . '\r\n';
            }
            $message .= "# time: ${trace['trace_time']} " . '\r\n';
            $t       = $trace['trace_time'] - $prevtime;
            $message .= "# consuming: $t" . '\r\n';
            if ($ignoreData == false) {
                $ps      = serialize($trace['plugin_setting']);
                $message .= "# input data: ${ps}" . '\r\n';
                $cd      = serialize($trace['context_data']);
                $message .= "# context data: ${cd}" . '\r\n';
            }
            $prevtime = $trace['trace_time'];
        }
        $message .= "# ${traceKey} end " . '\r\n';
        $message .= "# time: ${endInfo['trace_time']}" . '\r\n';
        $et      = $endInfo['trace_time'] - $prevtime;
        $message .= "# consuming: ${et}";
        $tt      = $endInfo['trace_time'] - $startInfo['trace_time'];
        $message .= "# total consuming: $tt" . '\r\n';
        if (isset($_SERVER['REQUEST_URI'])) {
            $message .= "# REQUEST_URI: ${_SERVER['REQUEST_URI']}" . '\r\n';
        } else if (!empty($_SERVER['argv'])) {
            $argv    = implode(' ', $_SERVER['argv']);
            $message .= "# Argv: ${argv}" . '\r\n';
        } else if (isset($_SERVER['SCRIPT_NAME'])) {
            $message .= "# SCRIPT_NAME: ${_SERVER['SCRIPT_NAME']}" . '\r\n';
        }
        self::log($message, Logger::LOEYE_LOGGER_TYPE_CONTEXT_TRACE, ['file' => __FILE__, 'line' => __LINE__]);
    }

}