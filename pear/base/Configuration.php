<?php

/**
 * Configuration.php
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
 * Description of Configuration
 *
 * @author   Zhang Yi <loeyae@gmail.com>
 */
class Configuration
{

    protected $property;
    private $_baseDir;
    private $_baseBundle;
    private $_baseContext;
    private $_bundle;
    private $_context;
    private $_config;

    const ENV_TAG = '${';

    /**
     * __construct
     *
     * @param string $bundle        bundle
     * @param string $context       context
     *
     * @return void
     */
    public function __construct($property, $bundle, $context = null)
    {
        $this->property     = $property;
        $this->_baseDir     = PROJECT_CONFIG_DIR . DIRECTORY_SEPARATOR . $property;
        $this->_baseBundle  = $bundle;
        $this->_baseContext = $context;
        $this->bundle($bundle, $context);
    }

    /**
     * getBaseDir
     *
     * @return string
     */
    public function getBaseDir()
    {
        return $this->_baseDir;
    }

    /**
     * getCurrentBundle
     *
     * @return string
     */
    public function getBundle()
    {
        return $this->_bundle ?? $this->_baseBundle;
    }

    /**
     * getContext
     *
     * @return mixed
     */
    public function getContext()
    {
        return $this->_context ?? $this->_baseContext;
    }

    /**
     * bundle
     *
     * @param string $bundle  boudle
     * @param string $context context
     *
     * @return mixed
     */
    public function bundle($bundle, $context = null)
    {
        $this->_bundle = $bundle;
        $this->context($context);
    }

    /**
     * context
     *
     * @param string $context context
     *
     * @return void
     */
    public function context($context = null)
    {
        $this->_context = $context;
        $this->_loadConfig();
    }

    /**
     * get
     *
     * @param string $key     key
     * @param string $default default
     *
     * @return null
     */
    public function get($key, $default = null)
    {
        $keyList = explode(".", $key);

        $config = $this->_config;
        $isList = true;
        foreach ($keyList as $k) {
            $config = $this->_getConfig($k, $config, $isList);
            if (null === $config) {
                return $default;
            }
            $isList = false;
        }
        return $config;
    }

    /**
     * getConfig
     *
     * @param string $bundle  bundle
     * @param string $context context
     *
     * @return mixed
     */
    public function getConfig($bundle = null, $context = null)
    {
        if (!empty($bundle)) {
            $this->bundle($bundle, $context);
        } else if (!empty($context)) {
            $this->context($context);
        }
        return $this->_config;
    }

    /**
     * getSettings
     *
     * @param string $bundle bundle
     * @param bool   $reduce need reduce
     *
     * @return array
     */
    public function getSettings($bundle = null, $reduce = true)
    {
        if (!empty($bundle)) {
            $this->bundle($bundle, null);
        }
        if ($reduce) {
            return array_reduce($this->_config, function($carry, $item) {
                return array_replace_recursive($carry, $item);
            }, []);
        }
        return $this->_config;
    }

    private function _loadConfig()
    {
        $cache  = \loeye\lib\ConfigCache::getInstance($this->property);
        $bundle = $this->getBundle() ?? '%2F';
        if (!$cache->has($bundle)) {
            $config = $this->_initConfig();
            $cache->set($bundle, $config);
        } else {
            $config = $cache->get($bundle);
        }
        $config  = $this->_initConfig();
        $context = $this->getContext() ?? 'master';
        if ($context == 'master') {
            $this->_config = $config['master'] ?? [];
        } else {
            list($k, $v) = preg_split('[=]', $context);
            $this->_config = $config[$k][$v] ?? [];
        }
    }

    /**
     * _initConfig
     *
     * @return void
     * @throws Exception
     */
    private function _initConfig()
    {
        $dir = realpath($this->_baseDir . '/' . ($this->getBundle()));
        if (!$dir) {
            throw new \loeye\error\ResourceException(\loeye\error\ResourceException::BUMDLE_NOT_FOUND_MSG,
                    \loeye\error\ResourceException::BUNDLE_NOT_FOUND_CODE, ["Bundle" => $dir]);
        }
        $fileSystem = new \FilesystemIterator($dir, \FilesystemIterator::KEY_AS_FILENAME);

        $config = [];
        foreach ($fileSystem as $file) {
            if ($file->isFile()) {
                $data    = $this->_parseFile(new \SplFileObject($file->getRealPath()));
                $iniData = $this->_initData($data);
                if (isset($iniData['master'])) {
                    $config['master'] = isset($config['master']) ? array_merge($config['master'], $iniData['master']) : $iniData['master'];
                    unset($iniData['master']);
                }
                if ($iniData) {
                    $config = array_replace_recursive($config, $iniData);
                }
            }
        }
        return $config;
    }

    /**
     * _parseFile
     *
     * @param \SplFileObject $fileObj SplFileObject
     *
     * @return array
     */
    private function _parseFile(\SplFileObject $fileObj)
    {
        $fileType = $fileObj->getExtension();
        if ($fileType == 'php') {
            return include $fileObj->getRealPath();
        }
        $fileObj->rewind();
        $content = '';
        ob_start();
        $fileObj->fpassthru();
        $content = ob_get_clean();
        switch ($fileType) {
            case 'json':
                return json_decode($content, true);
                break;
            case 'xml':
                $xmlRender = new \loeye\render\XmlRender();
                return $xmlRender->xml2array($content);
                break;
            case 'yaml':
            case 'yml':
                if (function_exists("yaml_parse")) {
                    return yaml_parse($content);
                } else {
                    return \Symfony\Component\Yaml\Yaml::parse($content);
                }
                break;
            default :
                return false;
        }
    }

    /**
     * _getConfig
     *
     * @param string $key    key
     * @param array  $config config
     * @param bool   $isList is list
     *
     * @return mixed
     */
    private function _getConfig($key, $config, $isList = false)
    {
        if ($isList) {
            foreach ($config as $item) {
                if (array_key_exists($key, $item)) {
                    return $this->_getEnv($item[$key]);
                }
            }
            return null;
        }
        return isset($config[$key]) ? $this->_getEnv($config[$key]) : null;
    }

    /**
     * 获取环境变量
     *
     * @param type $var
     * @return type
     */
    private function _getEnv($var) {
        if($var && Utils::startwith($var, self::ENV_TAG)) {
            $l = mb_strlen(self::ENV_TAG);
            $envSetting = mb_substr($var, $l, - 1);
            $envArray = explode(":", $envSetting);
            $key = $envArray[0];
            $default = isset($envArray[1]) ? $envArray : null;
            return getenv($key) ?: $default;
        }
        return $var;
    }

    /**
     * _initData
     *
     * @param array  data
     *
     * @return array
     */
    private function _initData($data)
    {
        $settins = [];
        foreach ($data as $item) {
            $rule  = $item['settings'] ?? [];
            unset($item['settings']);
            $title = $rule[0] ?? null;
            if (!$title) {
                continue;
            }
            if ($title == 'master') {
                $settins['master'] = isset($settins['master']) ? array_merge($settins['master'], [$item]) : [$item];
            } else if (is_array($title)) {
                $k = array_keys($title)[0] ?? null;
                $v = array_values($title)[0] ?? null;
                if (!$k || !$v) {
                    continue;
                }
                if (isset($settins[$k])) {
                    $settins[$k] = array_replace_recursive($settins[$k], [$v => $item]);
                } else {
                    $settins[$k] = [$v => $item];
                }
            }
        }
        return $settins;
    }

}
