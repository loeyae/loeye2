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

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\PersistentCollection;
use Doctrine\ORM\Tools\Pagination\Paginator;
use IntlDateFormatter;
use loeye\client\Client;
use loeye\error\{BusinessException, DataException, LogicException, ResourceException};
use loeye\database\Entity;
use loeye\web\Dispatcher;
use loeye\web\Template;
use Psr\Cache\InvalidArgumentException;
use ReflectionClass;
use ReflectionException;
use ReflectionMethod;
use SmartyException;
use Symfony\Component\Cache\Exception\CacheException;
use Throwable;

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
     * @param Client $client client
     * @param Context $context context
     *
     * @return void
     */
    public static function addParallelClient(Client $client, Context $context): void
    {
        $context->getParallelClientManager()->addClient($client);
    }

    /**
     * dateFormat
     *
     * @param string $locale locale
     * @param int $time time
     * @param string $timezone timezone
     * @param int $dateType date type
     * @param int $timeType time type
     * @param string $pattern pattern
     *
     * @return string
     */
    public static function dateFormat(
        $locale, $time, $timezone = 'Asia/Shanghai',
        $dateType = IntlDateFormatter::LONG, $timeType = IntlDateFormatter::MEDIUM,
        $pattern = 'yyyy-MM-dd HH:mm:ss'
    ): string
    {
        $calendar = IntlDateFormatter::GREGORIAN;
        $dateFormat = datefmt_create($locale, $dateType, $timeType, $timezone, $calendar, $pattern);
        return datefmt_format($dateFormat, $time);
    }

    /**
     * checkNotNull
     *
     * @param mixed $var
     * @throws DataException
     */
    public static function checkNotNull($var): void
    {
        if ($var === null) {
            throw new DataException(DataException::DATA_NOT_FOUND_ERROR_MSG, DataException::DATA_NOT_FOUND_ERROR_CODE);
        }
    }

    /**
     * checkValue
     *
     * @param mixed $data data
     * @param mixed $value value
     * @param string $key key
     *
     * @return mixed
     * @throws Exception
     */
    public static function checkValue($data, $value, $key = null)
    {
        if (empty($key)) {
            if ($data !== $value) {
                self::throwException(DataException::DATA_NOT_EQUALS_MSG, DataException::DATA_NOT_EQUALS, ['expected' => $value, 'actual' => $data], DataException::class);
            }
            return $data;
        }

        if ($data instanceof Context) {
            $origin = self::checkKeyExist($data, $key);
            if ($origin !== $value) {
                self::throwException(DataException::CONTEXT_VALUE_NOT_EQUALS_MSG, DataException::CONTEXT_VALUE_NOT_EQUALS, ['key' => $key, 'expected' => $value], DataException::class);
            }
            return $origin;
        }

        if (is_array($data)) {
            self::checkKeyExist($data, $key);
            $origin = $data[$key];
            if ($origin !== $value) {
                self::throwException(DataException::ARRAY_VALUE_NOT_EQUALS_MSG, DataException::ARRAY_VALUE_NOT_EQUALS, ['key' => $key, 'data' => print_r($data, true), 'expected' => $value], DataException::class);
            }
            return $origin;
        }

        if ($data !== $value) {
            self::throwException(DataException::DATA_NOT_EQUALS_MSG, DataException::DATA_NOT_EQUALS, ['expected' => $value, 'actual' => $data], DataException::class);
        }
        return $data;
    }

    /**
     * checkKeyExist
     *
     * @param Context|array $data data
     * @param string $key key
     *
     * @return mixed
     */
    public static function checkKeyExist($data, $key)
    {
        if ($data instanceof Context) {
            if (!$data->isExistKey($key)) {
                self::throwException(LogicException::CONTEXT_KEY_NOT_FOUND_MSG,
                    LogicException::CONTEXT_KEY_NOT_FOUND, ['key' => $key], LogicException::class);
            }
            return $data->get($key);
        }

        if (is_array($data)) {
            if (!isset($data[$key])) {
                self::throwException(LogicException::DATA_KEY_NOT_FOUND_MSG,
                    LogicException::DATA_KEY_NOT_FOUND, ['key' => $key, 'data' => print_r($data, true)], LogicException::class);
            }
            return $data[$key];
        }

        self::throwException(LogicException::DATA_KEY_NOT_FOUND_MSG, LogicException::DATA_KEY_NOT_FOUND, ['key' =>
            $key, 'data' => print_r($data, true)], LogicException::class);
        return null;
    }

    /**
     * checkNotEmpty
     *
     * @param Context|array $data data
     * @param string $key key
     * @param boolean $ignore ignore 0
     *
     * @return mixed
     */
    public static function checkNotEmpty($data, $key, $ignore = true)
    {
        if ($data instanceof Context) {
            if ($data->isEmpty($key, $ignore)) {
                self::throwException(LogicException::CONTEXT_VALUE_IS_EMPTY_MSG,
                    LogicException::CONTEXT_VALUE_IS_EMPTY, ['key' => $key], LogicException::class);
            }
            return $data->get($key);
        }

        if (is_array($data)) {
            if (!isset($data[$key])) {
                self::throwException(LogicException::DATA_VALUE_IS_EMPTY_MSG,
                    LogicException::DATA_VALUE_IS_EMPTY, ['key' => $key, 'data' => print_r($data, true)],
                    LogicException::class);
            }
            if ($ignore) {
                if (empty($data[$key]) && !is_numeric($data[$key])) {
                    self::throwException(LogicException::DATA_VALUE_IS_EMPTY_MSG,
                        LogicException::DATA_VALUE_IS_EMPTY, ['key' => $key, 'data' => print_r($data, true)],
                        LogicException::class);
                }
            } else if (empty($data[$key])) {
                self::throwException(LogicException::DATA_VALUE_IS_EMPTY_MSG,
                    LogicException::DATA_VALUE_IS_EMPTY, ['key' => $key, 'data' => print_r($data, true)],
                    LogicException::class);
            }
            return $data[$key];
        }

        self::throwException(LogicException::DATA_VALUE_IS_EMPTY_MSG, LogicException::DATA_VALUE_IS_EMPTY, ['key' =>
            $key, 'data' => print_r($data, true)], LogicException::class);
        return null;
    }

    /**
     * addErrors
     *
     * @param mixed $errors errors
     * @param Context $context context
     * @param array $setting setting
     * @param string $default default
     *
     * @return void
     */
    public static function addErrors($errors, Context $context, $setting, $default = null): void
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
            self::throwException('error key for set not exists in setting',
                BusinessException::INVALID_PLUGIN_SET_CODE, [], BusinessException::class);
        }
        $context->addErrors($key, $errors);
    }

    /**
     * getErrors
     *
     * @param Context $context context
     * @param array $setting setting
     * @param string $default default key
     *
     * @return mixed
     */
    public static function getErrors(Context $context, $setting, $default = null)
    {
        $key = null;
        if (!empty($setting['err'])) {
            $key = $setting['err'];
        } else if (!empty($setting['err_key'])) {
            $key = $setting['err_key'];
        } else if (!empty($default)) {
            $key = $default;
        } else {
            self::throwException('error key for get not exists in setting',
                BusinessException::INVALID_PLUGIN_SET_CODE, [], BusinessException::class);
        }
        return $context->getErrors($key);
    }

    /**
     * removeErrors
     *
     * @param Context $context context
     * @param array $setting setting
     * @param string $default default key
     *
     * @return void
     */
    public static function removeErrors(Context $context, $setting, $default = null): void
    {
        $key = null;
        if (!empty($setting['err'])) {
            $key = $setting['err'];
        } else if (!empty($setting['err_key'])) {
            $key = $setting['err_key'];
        } else if (!empty($default)) {
            $key = $default;
        } else {
            self::throwException('error key for remove not exists in setting',
                BusinessException::INVALID_PLUGIN_SET_CODE, [], BusinessException::class);
        }
        $context->removeErrors($key);
    }

    /**
     * setContextData
     *
     * @param mixed $data data
     * @param Context $context context
     * @param array $setting setting
     * @param string $default default
     *
     * @return void
     */
    public static function setContextData($data, Context $context, $setting, $default = null): void
    {
        if ($data === null) {
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
            self::throwException('data key for set not exists in setting',
                BusinessException::INVALID_PLUGIN_SET_CODE, [], BusinessException::class);
        }
        $expire = isset($setting['expire']) ? intval($setting['expire']) : null;
        $context->set($key, $data, $expire);
    }

    /**
     * checkContextCacheData
     *
     * @param Context $context context
     * @param array $setting setting
     * @param string $default default
     *
     * @return boolean
     */
    public static function checkContextCacheData(Context $context, $setting, $default = null): bool
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
     * @param Context $context context
     * @param array $setting setting
     * @param string $default default
     *
     * @return void
     */
    public static function unsetContextData(Context $context, $setting, $default = null): void
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
            self::throwException('data key for unset not exists in setting',
                BusinessException::INVALID_PLUGIN_SET_CODE, [], BusinessException::class);
        }
        $context->unsetKey($key);
    }

    /**
     * getContextData
     *
     * @param Context $context context
     * @param array $setting setting
     * @param string $default default
     *
     * @return mixed
     */
    public static function getContextData(Context $context, $setting, $default = null)
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
            self::throwException('data key for get not exists in setting',
                BusinessException::INVALID_PLUGIN_SET_CODE, [], BusinessException::class);
        }
        return $context->get($key);
    }

    /**
     * checkNotEmptyContextData
     *
     * @param Context $context context
     * @param array $setting setting
     * @param string $default default
     * @param bool $ignore is ignore 0
     *
     * @return mixed
     */
    public static function checkNotEmptyContextData(Context $context, $setting, $default = null, $ignore = true)
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
            self::throwException('input key not exists in setting', BusinessException::INVALID_PLUGIN_SET_CODE, [],
                BusinessException::class);
        }
        return self::checkNotEmpty($context, $key, $ignore);
    }

    /**
     * getData
     *
     * @param mixed $data data
     * @param string $key key
     * @param mixed $default default
     *
     * @return mixed
     */
    public static function getData($data, $key, $default = null)
    {
        if ($data instanceof Context) {
            return $data->get($key, $default);
        }

        if (is_array($data) && isset($data[$key])) {
            return $data[$key];
        }
        return $default;
    }

    /**
     * keyFilter
     *
     * @param Context|array $data data
     * @param array $required required keys
     * @param array $options options keys
     * @param array $least least one exist keys
     * @param boolean $ignore ignore 0
     *
     * @return array
     */
    public static function keyFilter(
        $data, array $required = array(), array $options = array(), array $least = array(), $ignore = true
    ): array
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
                self::throwException(LogicException::DATA_AT_LEAST_EXIST_ONE_KEY_ERROR,
                    LogicException::DATA_AT_LEAST_EXIST_ONE_KEY, ['keyList' => implode(',', $least), 'data' =>
                        $data], LogicException::class);
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
     * @param mixed $result result
     * @param mixed &$data data
     * @param \Exception &$error error
     *
     * @return void
     */
    public static function filterResult($result, &$data, &$error): void
    {
        if ($result instanceof Throwable) {
            $error = $result;
        } else {
            $data = $result;
        }
    }

    /**
     * filterResultArray
     *
     * @param array $result result
     * @param array &$data data
     * @param mixed &$errors errors
     *
     * @return void
     */
    public static function filterResultArray($result, &$data, &$errors): void
    {
        $data = array();
        if ($result instanceof \Exception) {
            $errors = $result;
        } else if (is_array($result)) {
            $errors = [];
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
     * @return bool
     */
    public static function hasException($result): bool
    {
        return $result instanceof \Exception;
    }

    /**
     * throwException
     *
     * @param string $errorMsg error message
     * @param int $errorCode error code
     * @param array $parameter parameter
     * @param string $exception class name
     *
     * @return void
     */
    public static function throwException($errorMsg, $errorCode = 500, array $parameter = [], $exception = null): void
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
     */
    public static function throwError($error): void
    {
        if (self::hasException($error)) {
            throw $error;
        }

        self::throwException(var_export($error, true));
    }

    /**
     * mbUcfirst
     *
     * @param string $string string
     *
     * @return string
     */
    public static function mbUcfirst($string): string
    {
        $ucFirstChar = mb_strtoupper(mb_substr($string, 0, 1));
        return ($ucFirstChar . mb_substr($string, 1));
    }

    /**
     * includeView
     *
     * @param string $file file
     * @param array $parameter parameter
     *
     * @return void
     */
    public static function includeView($file, array $parameter = array()): void
    {
        if (!is_file($file)) {
            $dno = mb_strrpos($file, '.');
            $file = PROJECT_VIEWS_DIR . '/'
                . str_replace('.', '/', mb_substr($file, 0, $dno)) . mb_substr($file, $dno);
        }
        if (is_array($parameter) && !empty($parameter)) {
            extract($parameter, EXTR_OVERWRITE);
        }
        include $file;
    }

    /**
     * setPageCache
     *
     * @param AppConfig $appConfig appConfig
     * @param string $moduleId module id
     * @param string $page page source
     * @param int $expire expire
     * @param array $params params
     *
     * @return void
     * @throws Exception
     * @throws CacheException
     */
    public static function setPageCache(AppConfig $appConfig, $moduleId, $page, $expire = 0, $params = []): void
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
     * @param AppConfig $appConfig appConfig
     * @param string $moduleId module id
     * @param array $params params
     *
     * @return string|null
     * @throws CacheException
     * @throws Exception
     * @throws InvalidArgumentException
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
     * @param Context $context context
     * @param string $file file name
     *
     * @return void
     * @throws ResourceException
     * @throws SmartyException
     */
    public static function includeTpl(Context $context, $file): void
    {
        if (!is_file($file)) {
            $dno = mb_strrpos($file, '.');
            $file = PROJECT_VIEWS_DIR . '/'
                . str_replace('.', '/', mb_substr($file, 0, $dno)) . mb_substr($file, $dno);
        }
        $template = $context->getTemplate();
        if (!($template instanceof Template)) {
            include $file;
        }

        echo $template->fetch($file);
    }

    /**
     * includeModule
     *
     * @param string $moduleId module id
     * @param array $parameter parameter
     *
     * @return void
     */
    public static function includeModule($moduleId, $parameter = array()): void
    {
        if (!empty($parameter)) {
            foreach ((array)$parameter as $key => $value) {
                if (is_numeric($key)) {
                    continue;
                }
                $_REQUEST[$key] = $value;
            }
        }
        $dispatcher = new Dispatcher();
        $dispatcher->dispatch($moduleId);
    }

    /**
     * usc2ToUtf8
     *
     * @param string $string string
     *
     * @return string
     */
    public static function usc2ToUtf8($string): string
    {
        return preg_replace_callback("#\\\u([0-9a-f]{4})#i", static function ($matches) {
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
    public static function asciiToUtf8($string): string
    {
        $matches = array();
        $offset = 0;
        $decode = '';
        $string = mb_strtolower($string);
        if (mb_strpos($string, 'x') !== false) {
            while (preg_match('#x[0-f]{2}#', $string, $matches, PREG_OFFSET_CAPTURE, $offset)) {
                $code = $matches[0][0];
                $decode .= chr('0' . $code);
                $offset = $matches[0][1] + mb_strlen($code);
            }
        } else {
            while (preg_match('#\d{2}#', $string, $matches, PREG_OFFSET_CAPTURE, $offset)) {
                $code = $matches[0][0];
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
    public static function getArrayLevel($array): int
    {
        if (is_array($array)) {
            $arr = [];
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
     * @param mixed $data data
     * @param mixed $setting setting
     *
     * @return mixed
     */
    public static function callUserFuncArray($data, $setting)
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
                $object = new $setting['class'];
                $callback = array(
                    $object,
                    $function,
                );
                unset($setting['class'], $setting['src'], $setting['method']);
            } else {
                $callback = $setting;
            }
            $parameterArr = array($data);
            if (isset($setting['param'])) {
                $parameter = (array)$setting['param'];
            } else {
                $parameter = (array)$setting;
            }
            return call_user_func_array($callback, array_merge($parameterArr, $parameter));
        } catch (\Exception $e) {
            self::errorLog($e);
            self::throwException(
                BusinessException::INVALID_PARAMETER_MSG, BusinessException::INVALID_PARAMETER_CODE, [], BusinessException::class);
        }
        return null;
    }

    /**
     * log
     *
     * @param mixed $message message
     * @param int $messageType message type
     * @param array $trace trace info
     *
     * @return void
     */
    public static function log($message, $messageType = Logger::LOEYE_LOGGER_TYPE_NOTICE, $trace = []): void
    {
        $name = defined('PROJECT_PROPERTY') ? PROJECT_PROPERTY : PROJECT_NAMESPACE;
        if ($messageType === Logger::LOEYE_LOGGER_TYPE_CONTEXT_TRACE) {
            $logfile = RUNTIME_LOG_DIR . DIRECTORY_SEPARATOR
                . PROJECT_NAMESPACE . DIRECTORY_SEPARATOR . 'trace-' . $name . '.log';
            $messageType = Logger::LOEYE_LOGGER_TYPE_DEBUG;
        } else {
            $logfile = RUNTIME_LOG_DIR . DIRECTORY_SEPARATOR
                . PROJECT_NAMESPACE . DIRECTORY_SEPARATOR . 'error-' . $name . '.log';
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
     * @param Throwable $exc exception
     *
     * @return void
     */
    public static function errorLog(Throwable $exc): void
    {
        $file = $exc->getFile();
        if ($file === __FILE__) {
            return;
        }
        $line = $exc->getLine();
        $message = $exc->getMessage();
        $code = $exc->getCode();
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
     * @param int $no no
     * @param string $message message
     * @param string $file file
     * @param int $line line
     *
     * @return void
     */
    public static function errorHandle($no, $message, $file, $line): void
    {
        Logger::handle($no, $message, $file, $line);
    }

    /**
     * setTraceDataIntoContext
     *
     * @param Context $context context
     * @param array $pluginSetting plugin setting
     * @param string $traceKey trace key
     *
     * @staticvar int            $traceCount   trace count
     *
     * @return void
     */
    public static function setTraceDataIntoContext(Context $context, $pluginSetting = array(), $traceKey = null): void
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
                'trace_time' => microtime(true),
                'context_data' => $contextData,
            );
        } else {
            $trace[$traceCount] = array(
                'trace_time' => microtime(true),
                'context_data' => $contextData,
                'plugin_setting' => $pluginSetting,
            );
        }
        $traceCount++;
        $context->setTraceData($traceKey, $trace);
    }

    /**
     * logContextTrace
     *
     * @param Context $context context
     * @param string $traceKey trace key
     * @param bool $ignoreData ignore data
     *
     * @return void
     */
    public static function logContextTrace(Context $context, $traceKey = null, $ignoreData = true): void
    {
        if (!$traceKey) {
            $traceKey = LOEYE_CONTEXT_TRACE_KEY;
        }
        $traceInfo = $context->getTraceData($traceKey);
        if (empty($traceInfo)) {
            return;
        }
        $startInfo = array_shift($traceInfo);
        $message = ['context trace info:'];
        $message[] = "# ${traceKey} init";
        $message[] = "#  time: ${startInfo['trace_time']}";
        if (count($traceInfo) > 1 && empty(current($traceInfo)['plugin_setting'])) {
            $pluginStartInfo = array_shift($traceInfo);
            $message[] = '# start ';
            $message[] = "#  time: ${pluginStartInfo['trace_time']}";
            $t = $pluginStartInfo['trace_time'] - $startInfo['trace_time'];
            $message[] = "#  consuming: $t";
            if ($ignoreData === false) {
                $message[] = '#  current context: ' . json_encode($pluginStartInfo['context_data']);
            }
            $prevTime = $pluginStartInfo['trace_time'];
        } else {
            $prevTime = $startInfo['trace_time'];
        }
        $endInfo = end($traceInfo);
        if (!isset($endInfo['plugin_setting'])) {
            array_pop($traceInfo);
        }
        reset($traceInfo);
        foreach ($traceInfo as $trace) {
            if (isset($trace['plugin_setting']['name'])) {
                $p = $trace['plugin_setting']['name'];
                $message[] = "# ${p}";
            } else {
                $message[] = "# ${traceKey} process ";
            }
            $message[] = "# time: ${trace['trace_time']} ";
            $t = $trace['trace_time'] - $prevTime;
            $message[] = "# consuming: $t";
            if ($ignoreData === false) {
                $message[] = '# plugin setting: ' . json_encode($trace['plugin_setting']);
                $message[] = '# current context: ' . json_encode($trace['context_data']);
            }
            $prevTime = $trace['trace_time'];
        }
        $message[] = "# ${traceKey} end ";
        $message[] = "# time: ${endInfo['trace_time']}";
        $et = $endInfo['trace_time'] - $prevTime;
        $message[] = "# consuming: ${et}";
        $tt = $endInfo['trace_time'] - $startInfo['trace_time'];
        $message[] = "# total consuming: $tt";
        if (isset($_SERVER['REQUEST_URI'])) {
            $message[] = "# REQUEST_URI: ${_SERVER['REQUEST_URI']}";
        } else if (!empty($_SERVER['argv'])) {
            $argv = implode(' ', $_SERVER['argv']);
            $message[] = "# Argv: ${argv}";
        } else if (isset($_SERVER['SCRIPT_NAME'])) {
            $message[] = "# SCRIPT_NAME: ${_SERVER['SCRIPT_NAME']}";
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
    public static function startWith($str1, $str2): bool
    {
        return strpos($str1, $str2) === 0;
    }

    /**
     * endWith
     *
     * @param string $str1
     * @param string $str2
     * @return bool
     */
    public static function endWith($str1, $str2): bool
    {
        return substr_compare($str1, $str2, -strlen($str2)) === 0;
    }

    /**
     * 　　* 下划线转驼峰
     * 　　* 思路:
     * 　　* step1.原字符串转小写,原字符串中的分隔符用空格替换,在字符串开头加上分隔符
     * 　　* step2.将字符串中每个单词的首字母转换为大写,再去空格,去字符串首部附加的分隔符.
     *
     * @param string $unCamelizeWords
     * @param string $separator
     * @return string
     */
    public static function camelize($unCamelizeWords, $separator = '_'): string
    {
        $unCamelizeWords = $separator . str_replace($separator, ' ', strtolower($unCamelizeWords));
        return ltrim(str_replace(' ', '', ucwords($unCamelizeWords)), $separator);
    }

    /**
     * 　 * 驼峰命名转下划线命名
     * 　 * 思路:
     * 　 * 小写和大写紧挨一起的地方,加上分隔符,然后全部转小写
     *
     * @param string $camelCaps
     * @param string $separator
     * @return string
     */
    public static function uncamelize($camelCaps, $separator = '_'): string
    {
        return strtolower(preg_replace('/([a-z])([A-Z])/', '$1' . $separator . '$2', $camelCaps));
    }

    /**
     * setWriteMethodValue
     *
     * @param object $entity instance of object
     * @param string $field field
     * @param mixed $value value
     * @return void
     * @throws ReflectionException
     */
    public static function setWriteMethodValue($entity, $field, $value): void
    {
        $method = 'set' . ucfirst($field);
        if (method_exists($entity, $method)) {
            $refMethod = new ReflectionMethod($entity, $method);
            $refMethod->invokeArgs($entity, [$value]);
        }
    }

    /**
     * getReadMethodValue
     *
     * @param object $entity instance of object
     * @param string $field field
     * @return mixed
     * @throws ReflectionException
     */
    public static function getReadMethodValue($entity, $field)
    {
        $method = 'get' . ucfirst($field);
        $value = null;
        if (method_exists($entity, $method)) {
            $refMethod = new ReflectionMethod($entity, $method);
            $value = $refMethod->invoke($entity);
        }
        return $value;
    }

    /**
     * source2entity
     *
     * @param array|object $source
     * @param string $class
     *
     * @return object
     * @throws ReflectionException
     */
    public static function source2entity($source, $class)
    {
        $rfc = new ReflectionClass($class);
        $object = $rfc->newInstanceArgs();
        self::copyProperties($source, $object);
        return $object;
    }

    /**
     * copy properties
     *
     * @param array|object $source
     * @param object $object
     * @return object
     * @throws ReflectionException
     */
    public static function copyProperties($source, $object)
    {
        if (is_array($source)) {
            foreach ($source as $key => $value) {
                self::setWriteMethodValue($object, self::camelize($key), $value);
            }
        } else if (is_object($source)) {
            $sourceRefClass = new ReflectionClass($source);
            $methodList = $sourceRefClass->getMethods(ReflectionMethod::IS_PUBLIC);
            foreach ($methodList as $method) {
                $methodName = $method->getName();
                if (self::startWith($methodName, "get")) {
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
     * @param array $source source data
     * @param string $target target class name
     * @return array
     * @throws ReflectionException
     */
    public static function copyListProperties($source, $target): array
    {
        $out = [];
        if (empty($source)) {
            return $out;
        }
        $tar = new ReflectionClass($target);
        foreach ($source as $src) {
            $out[] = self::copyProperties($src, $tar->newInstanceArgs());
        }
        return $out;
    }

    /**
     * convert entity to array
     *
     * @param EntityManager $em entity manager
     * @param mixed $entity entity
     * @param array $ignore ignore
     * @return mixed
     * @throws ReflectionException
     */
    public static function entity2array(EntityManager $em, $entity, $ignore = [])
    {
        if (is_object($entity)) {
            $r = [];
            $class = get_class($entity);
            $ignore[] = $class;
            $metadata = $em->getClassMetadata($class);
            foreach ($metadata->fieldNames as $column => $field) {
                $r[$column] = self::getReadMethodValue($entity, $field);
            }
            foreach ($metadata->associationMappings as $key => $association) {
                $target = $association['targetEntity'];
                $ignoreClass = $ignore;
                if (in_array($target, $ignoreClass, true)) {
                    continue;
                }
                $rs = null;
                $ignoreClass[] = $target;
                $value = self::getReadMethodValue($entity, $key);
                if ($value instanceof \Doctrine\Common\Collections\AbstractLazyCollection) {
                    $rs = self::entities2array($em, $value->toArray(), $ignoreClass);
                } if (is_array($value)) {
                    $rs = self::entities2array($em, $value, $ignoreClass);
                } else if ($value instanceof Entity) {
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
     * @param EntityManager $em entity manager
     * @param array $entities entities
     * @param array $ignore ignore
     * @return array
     */
    public static function entities2array(EntityManager $em, $entities, $ignore = []): array
    {
        array_walk($entities, static function (&$item, $key, $uData) {
            $item = Utils::entity2array($uData['em'], $item, $uData['ignore']);
        }, ['em' => $em, 'ignore' => $ignore]);
        return $entities;
    }

    /**
     * paginator2array
     *
     * @param EntityManager $em
     * @param Paginator $paginator
     *
     * @return array
     * @throws ReflectionException
     */
    public static function paginator2array(EntityManager $em, Paginator $paginator): array
    {
        $result = array();
        foreach ($paginator as $post) {
            $result[] = self::entity2array($em, $post);
        }
        return ['total' => $paginator->count(), 'start' => $paginator->getQuery()->getFirstResult(), 'offset' =>
            $paginator->getQuery()->getMaxResults(), 'list' => $result];
    }

}
