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
 * @version  2018-07-23 22:44:28
 * @link     https://github.com/loeyae/loeye2.git
 */

namespace loeye\base;
use loeye\error\{LogicException, BusinessException, DataException};

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
     * @param \loeye\client\Client $client  client
     * @param \loeye\base\Context    $context context
     *
     * @return void
     */
    static public function addParallelClient(\loeye\client\Client $client, Context $context)
    {
        $context->getParallelClientManager()->addClient($client);
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
     * checkNotNull
     *
     * @param mixed $var
     * @throws DataException
     */
    static public function checkNotNull($var)
    {
        if (is_null($var)) {
            throw new DataException(DataException::DATA_NOT_FOUND_ERROR_MSG, DataException::DATA_NOT_FOUND_ERROR_CODE);
        }
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
                self::throwException(DataException::DATA_NOT_EQUALS_MSG, DataException::DATA_NOT_EQUALS, ['expected' => $value, 'actual' => $data], DataException::class);
            }
            return $data;
        } else if ($data instanceof Context) {
            self::checkKeyExist($data, $key);
            $origin = $data->get($key);
            if ($origin !== $value) {
                self::throwException(DataException::CONTEXT_VALUE_NOT_EQUALS_MSG, DataException::CONTEXT_VALUE_NOT_EQUALS, ['key' => $key, 'expected' => $value], DataException::class);
            }
            return $origin;
        } else if (is_array($data)) {
            self::checkKeyExist($data, $key);
            $origin = $data[$key];
            if ($origin !== $value) {
                self::throwException(DataException::ARRAY_VALUE_NOT_EQUALS_MSG, DataException::ARRAY_VALUE_NOT_EQUALS, ['key' => $key, 'data' => print_r($data, true), 'expected' => $value], DataException::class);
            }
            return $origin;
        } else {
            if ($data !== $value) {
                self::throwException(DataException::DATA_NOT_EQUALS_MSG, DataException::DATA_NOT_EQUALS, ['expected' => $value, 'actual' => $data], DataException::class);
            }
            return $data;
        }
    }

    /**
     * ckeckExist
     *
     * @param \loeye\base\Context|array $data data
     * @param string                    $key  key
     *
     * @return mixed
     */
    static public function ckeckExist($data, $key)
    {
        if ($data instanceof Context) {
            if (!$data->isExist($key)) {
                self::throwException(LogicException::CONTEXT_KEY_NOT_FOUND_MSG, LogicException::CONTEXT_KEY_NOT_FOUND, ['key' => $key], LogicException::class);
            }
            return $data->get($key);
        } else if (is_array($data)) {
            if (!isset($data[$key])) {
                self::throwException(LogicException::DATA_KEY_NOT_FOUND_MSG, LogicException::DATA_KEY_NOT_FOUND, ['key' => $key, 'data' => print_r($data, true)], LogicException::class);
            }
            return $data[$key];
        } else {
            self::throwException(LogicException::DATA_KEY_NOT_FOUND_MSG, LogicException::DATA_KEY_NOT_FOUND, ['key' => $key, 'data' => print_r($data, true)], LogicException::class);
        }
    }

    /**
     * checkKeyExist
     *
     * @param \loeye\base\Context|array $data data
     * @param string                    $key  key
     *
     * @return mixed
     */
    static public function checkKeyExist($data, $key)
    {
        if ($data instanceof Context) {
            if (!$data->isExistKey($key)) {
                self::throwException(LogicException::CONTEXT_KEY_NOT_FOUND_MSG, LogicException::CONTEXT_KEY_NOT_FOUND, ['key' => $key], LogicException::class);
            }
            return $data->get($key);
        } else if (is_array($data)) {
            if (!isset($data[$key])) {
                self::throwException(LogicException::DATA_KEY_NOT_FOUND_MSG, LogicException::DATA_KEY_NOT_FOUND, ['key' => $key, 'data' => print_r($data, true)], LogicException::class);
            }
            return $data[$key];
        } else {
            self::throwException(LogicException::DATA_KEY_NOT_FOUND_MSG, LogicException::DATA_KEY_NOT_FOUND, ['key' => $key, 'data' => print_r($data, true)], LogicException::class);
        }
    }

    /**
     * checkNotEmpty
     *
     * @param \loeye\base\Context|array $data   data
     * @param string                    $key    key
     * @param boolean                   $ignore ingore 0
     *
     * @return mixed
     */
    static public function checkNotEmpty($data, $key, $ignore = true)
    {
        if ($data instanceof Context) {
            if ($data->isEmpty($key, $ignore)) {
                self::throwException(LogicException::CONTEXT_VALUE_IS_EMPTY_MSG, LogicException::CONTEXT_VALUE_IS_EMPTY, ['key' => $key], LogicException::class);
            }
            return $data->get($key);
        } else if (is_array($data)) {
            if (!isset($data[$key])) {
                self::throwException(LogicException::DATA_VALUE_IS_EMPTY_MSG, LogicException::DATA_VALUE_IS_EMPTY, ['key' => $key, 'data' => print_r($data, true)], LogicException::class);
            }
            if ($ignore) {
                if (empty($data[$key]) && !is_numeric($data[$key])) {
                    self::throwException(LogicException::DATA_VALUE_IS_EMPTY_MSG, LogicException::DATA_VALUE_IS_EMPTY, ['key' => $key, 'data' => print_r($data, true)], LogicException::class);
                }
            } else {
                if (empty($data[$key])) {
                    self::throwException(LogicException::DATA_VALUE_IS_EMPTY_MSG, LogicException::DATA_VALUE_IS_EMPTY, ['key' => $key, 'data' => print_r($data, true)], LogicException::class);
                }
            }
            return $data[$key];
        } else {
                self::throwException(LogicException::DATA_VALUE_IS_EMPTY_MSG, LogicException::DATA_VALUE_IS_EMPTY, ['key' => $key, 'data' => print_r($data, true)], LogicException::class);
        }
    }

    /**
     * addErrors
     *
     * @param mixed               $errors  errors
     * @param \loeye\base\Context $context context
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
            self::throwException('error key for set not exists in setting', BusinessException::INVALID_PLUGIN_SET_CODE, [], BusinessException::class);
        }
        $context->addErrors($key, $errors);
    }

    /**
     * getErrors
     *
     * @param \loeye\base\Context $context context
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
            self::throwException('error key for get not exists in setting', BusinessException::INVALID_PLUGIN_SET_CODE, [], BusinessException::class);
        }
        return $context->getErrors($key);
    }

    /**
     * removeErrors
     *
     * @param \loeye\base\Context $context context
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
            self::throwException('error key for remove not exists in setting',  BusinessException::INVALID_PLUGIN_SET_CODE, [], BusinessException::class);
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
            self::throwException('data key for set not exists in setting',  BusinessException::INVALID_PLUGIN_SET_CODE, [], BusinessException::class);
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
    static public function checkContextCacheData(Context $context, $setting, $default = null): bool
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
            self::throwException('data key for unset not exists in setting',  BusinessException::INVALID_PLUGIN_SET_CODE, [], BusinessException::class);
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
            self::throwException("data key for get not exists in setting",  BusinessException::INVALID_PLUGIN_SET_CODE, [], BusinessException::class);
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
            self::throwException('input key not exists in setting',  BusinessException::INVALID_PLUGIN_SET_CODE, [], BusinessException::class);
        }
        return self::checkNotEmpty($context, $key, $ignore);
    }

    /**
     * getData
     *
     * @param mixed $data    data
     * @param string  $key     key
     * @param mixed  $default default
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
                self::throwException(LogicException::DATA_AT_LEAST_EXIST_ONE_KEY_ERROR, LogicException::DATA_AT_LEAST_EXIST_ONE_KEY, ['keyList' => implode(',', $least), 'data' => $data], LogicException::class);
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
     * @param array  $parameter parameter
     * @param string $exception class name
     *
     * @return void
     * @throws Exception
     */
    static public function throwException($errorMsg, $errorCode = 500, array $parameter = [], $exception = null)
    {
        $logMsg = "[logic] ${errorMsg}";
        self::log($logMsg, Logger::LOEYE_LOGGER_TYPE_ERROR);
        if (null === $exception) {
            $exception = Exception::class;
        }
        throw new $exception($errorMsg, $errorCode, $parameter);
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
        $cache->set($fileKey, $page, $expire);
    }

    /**
     * getPageCache
     *
     * @param \loeye\base\AppConfig $appConfig appConfig
     * @param string                $moduleId  module id
     * @param array                 $params    params
     *
     * @return string|null
     */
    public static function getPageCache(AppConfig $appConfig, $moduleId, $params = []): ?string
    {
        $fileKey = $moduleId;
        if (!empty($params)) {
            sort($params);
            $fileKey .= '?' . http_build_query($params);
        }
        $cache = Cache::getInstance($appConfig, 'templates');
        return $cache->get($fileKey);
    }

    /**
     * includeTpl
     *
     * @param \loeye\base\Context $context context
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
        $dispatcher->dispatch($moduleId);
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
                BusinessException::INVALID_PARAMETER_MSG, BusinessException::INVALID_PARAMETER_CODE, [], BusinessException::class);
        }
    }

    /**
     * log
     *
     * @param mixed $message     message
     * @param int   $messageType message type
     * @param array $trace       trace info
     *
     * @return void
     */
    static public function log($message, $messageType = Logger::LOEYE_LOGGER_TYPE_NOTICE, $trace = [])
    {
        $name = defined('PROJECT_PROPERTY') ? PROJECT_PROPERTY : PROJECT_NAMESPACE;
        if ($messageType == Logger::LOEYE_LOGGER_TYPE_CONTEXT_TRACE) {
            $logfile     = RUNTIME_LOG_DIR . DIRECTORY_SEPARATOR
                    . PROJECT_NAMESPACE . DIRECTORY_SEPARATOR . 'trace-'.$name .'.log';
            $messageType = Logger::LOEYE_LOGGER_TYPE_DEBUG;
        } else {
            $logfile = RUNTIME_LOG_DIR . DIRECTORY_SEPARATOR
                    . PROJECT_NAMESPACE . DIRECTORY_SEPARATOR . 'error-'. $name .'.log';
        }
        Logger::log($message, $messageType, $logfile);
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
                $message = "#$i {$t['file']}({$t['line']}): ";
                if (isset($t['class'])) {
                    $message .= "{$t['class']}->";
                }
                $message .= "{$t['function']}()";
                Logger::log($message, $messageType, $logfile);
            }
            if (isset($_SERVER['REQUEST_URI'])) {
                $message = "# REQUEST_URI: ${_SERVER['REQUEST_URI']}";
                Logger::log($message, $messageType, $logfile);
            }
        }
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
        $tracInfo = $context->getTraceData($traceKey);
        if (empty($tracInfo)) {
            return;
        }
        $startInfo = array_shift($tracInfo);
        $message   = ["context trace info:"];
        $message[] = "# ${traceKey} init";
        $message[] =  "#  time: ${startInfo['trace_time']}";
        if (count($tracInfo) > 1 && empty(current($tracInfo)['plugin_setting'])) {
            $pluginStartInfo = array_shift($tracInfo);
            $message[] =  "# start ";
            $message[] =  "#  time: ${pluginStartInfo['trace_time']}";
            $t               = $pluginStartInfo['trace_time'] - $startInfo['trace_time'];
            $message[] =  "#  consuming: $t";
            if ($ignoreData == false) {
                $message[] =  "#  current context: ". json_encode($pluginStartInfo['context_data']);
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
                $message[] =  "# ${p}";
            } else {
                $message[] =  "# ${traceKey} process ";
            }
            $message[] =  "# time: ${trace['trace_time']} ";
            $t       = $trace['trace_time'] - $prevtime;
            $message[] =  "# consuming: $t";
            if ($ignoreData == false) {
                $message[] = "# plugin setting: ". json_encode($trace['plugin_setting']);
                $message[] = "# current context: ". json_encode($trace['context_data']);
            }
            $prevtime = $trace['trace_time'];
        }
        $message[] =  "# ${traceKey} end ";
        $message[] =  "# time: ${endInfo['trace_time']}";
        $et      = $endInfo['trace_time'] - $prevtime;
        $message[] = "# consuming: ${et}";
        $tt      = $endInfo['trace_time'] - $startInfo['trace_time'];
        $message[] =  "# total consuming: $tt";
        if (isset($_SERVER['REQUEST_URI'])) {
            $message[] =  "# REQUEST_URI: ${_SERVER['REQUEST_URI']}";
        } else if (!empty($_SERVER['argv'])) {
            $argv    = implode(' ', $_SERVER['argv']);
            $message[] =  "# Argv: ${argv}";
        } else if (isset($_SERVER['SCRIPT_NAME'])) {
            $message[] =  "# SCRIPT_NAME: ${_SERVER['SCRIPT_NAME']}";
        }
        self::log($message, Logger::LOEYE_LOGGER_TYPE_CONTEXT_TRACE, ['file' => __FILE__, 'line' => __LINE__]);
    }

    /**
     * 判断字符串1是否以字符串2开头
     *
     * @param string $str1
     * @param string $str2
     * @return bool
     */
    static public function startwith($str1, $str2) {
        return strpos($str1, $str2) === 0;
    }

    /**
     *
     * @param type $str1
     * @param type $str2
     * @return type
     */
    static public function endwith($str1, $str2) {
        return substr_compare($str1, $str2, -strlen($str2)) === 0;
    }

    /**
　　* 下划线转驼峰
　　* 思路:
　　* step1.原字符串转小写,原字符串中的分隔符用空格替换,在字符串开头加上分隔符
　　* step2.将字符串中每个单词的首字母转换为大写,再去空格,去字符串首部附加的分隔符.
    *
    * @param string $uncamelizedWords
    * @param string $separator
    * @return string
    */
    static public function camelize($uncamelizedWords, $separator='_')
    {
        $uncamelizedWords = $separator. str_replace($separator, " ", strtolower($uncamelizedWords));
        return ltrim(str_replace(" ", "", ucwords($uncamelizedWords)), $separator );
    }

   /**
　 * 驼峰命名转下划线命名
　 * 思路:
　 * 小写和大写紧挨一起的地方,加上分隔符,然后全部转小写
   *
   * @param string $camelCaps
   * @param string $separator
   * @return string
   */
    static public  function uncamelize($camelCaps, $separator='_')
    {
        return strtolower(preg_replace('/([a-z])([A-Z])/', "$1" . $separator . "$2", $camelCaps));
    }

    /**
     * setWriteMethodValue
     *
     * @param object $entity
     * @param string $field
     * @param mixed  $value
     * @return void
     */
    static public function setWriteMethodValue($entity, $field, $value): void
    {
        $method = "set". ucfirst($field);
        if (method_exists($entity, $method)) {
            $refMethod = new \ReflectionMethod($entity, $method);
            $refMethod->invokeArgs($entity, [$value]);
        }
    }

    /**
     * getReadMethodValue
     *
     * @param object $entity
     * @param string $field
     * @return mixed
     */
    static public function getReadMethodValue($entity, $field)
    {
        $method = "get".ucfirst($field);
        $value = null;
        if (method_exists($entity, $method)) {
            $refMethod = new \ReflectionMethod($entity, $method);
            $value = $refMethod->invoke($entity);
        }
        return $value;
    }

    /**
     *
     * @param array|object $source
     * @param string       $class
     *
     * @return object
     */
    static public function source2entity($source, $class)
    {
        $rfc = new \ReflectionClass($class);
        $object = $rfc->newInstanceArgs();
        self::copyProperties($source, $object);
        return $object;
    }

    /**
     * copy properties
     *
     * @param array|object  $source
     * @param object        $object
     */
    static public function copyProperties($source, $object)
    {
        if (is_array($source)) {
            foreach ($source as $key => $value) {
                self::setWriteMethodValue($object, self::camelize($key), $value);
            }
        } else if (is_object($source)) {
            $sourceRefClass = new \ReflectionClass($source);
            $methodList = $sourceRefClass->getMethods(\ReflectionMethod::IS_PUBLIC);
            foreach ($methodList as $method) {
                $methodName = $method->getName();
                if (self::startwith($methodName, "get")) {
                    $value = $method->invokeArgs($source, []);
                    self::setWriteMethodValue($object, substr($methodName, 3), $value);
                }
            }
        }
        return $object;
    }

    /**
     * copy list properties
     *
     * @param array $source
     * @param type $target
     * @return array
     */
    static public function copyListProperties($source, $target) {
        $out = [];
        if (empty($source)) {
            return $out;
        }
        $tar = new \ReflectionClass($target);
        foreach ($source as $src) {
            $out[] = self::copyProperties($src, $tar->newInstanceArgs());
        }
        return $out;
    }

    /**
     * convert entity to array
     *
     * @param \Doctrine\ORM\EntityManager $em
     * @param object                      $entity
     * @param array                       $ignore
     * @return type
     */
    static public function entity2array(\Doctrine\ORM\EntityManager $em, $entity, $ignore=[])
    {
        if (is_object($entity)) {
            $r = [];
            $class = get_class($entity);
            $ignore[] = $class;
            $metadata = $em->getClassMetadata($class);
            foreach ($metadata->fieldNames as $field) {
                $r[$field] = self::getReadMethodValue($entity, $field);
            }
            foreach ($metadata->associationMappings as $key => $association) {
                $target = $association['targetEntity'];
                $ignoreClass = $ignore;
                if (in_array($target, $ignoreClass)) {
                    continue;
                }
                $rs = null;
                $ignoreClass[] = $target;
                $value = self::getReadMethodValue($entity, $key);
                if (is_array($value)) {
                   $rs = self::entities2array($em, $value, $ignoreClass);
                } else {
                    $rs = self::entity2array($em, $value, $ignoreClass);
                }
                $r[$key] = $rs;
            }
            return $r;
        }
        return $entity;
    }

    /**
     * convert entity list to array list
     *
     * @param \Doctrine\ORM\EntityManager $em
     * @param array                       $entities
     * @param array                       $ignore
     * @return type
     */
    static public function entities2array(\Doctrine\ORM\EntityManager $em, $entities, $ignore = [])
    {
        array_walk($entities, function(&$item, $key, $udata) {
            $item = Utils::entity2array($udata['em'], $item, $udata['ignore']);
        }, ['em'=>$em,'ignore'=>$ignore]);
        return $entities;
    }

    /**
     * paginator2array
     *
     * @param \Doctrine\ORM\EntityManager              $em
     * @param \Doctrine\ORM\Tools\Pagination\Paginator $paginator
     *
     * @return array
     */
    static public function paginator2array(\Doctrine\ORM\EntityManager $em, \Doctrine\ORM\Tools\Pagination\Paginator $paginator) {
        $result = array();
        foreach ($paginator as $post) {
            array_push($result, self::entity2array($em, $post));
        }
        return ["total" => $paginator->count(), "start" => $paginator->getQuery()->getFirstResult(), "offset" => $paginator->getQuery()->getMaxResults(), "list" => $result];
    }

}
