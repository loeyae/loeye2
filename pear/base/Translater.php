<?php

/**
 * LocalTranslater.php
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

use Symfony\Component\Translation as I18n;

/**
 * LocalTranslater
 *
 * @author   Zhang Yi <loeyae@gmail.com>
 */
class Translater {

    private $_locale = "zh_CN";

    /**
     *
     * @var Symfony\Component\Translation\Translator
     */
    protected $translater;


    /**
     * __construct
     *
     * @param \loeye\base\AppConfig $appConfig AppConfig instance
     */
    public function __construct(AppConfig $appConfig)
    {
        $this->_locale    = $appConfig->getLocale() ?? $this->_locale;
        $this->translater = new I18n\Translator($this->_locale);
        $loader           = new I18n\Loader\YamlFileLoader();
        $this->initFrameworkResource();
        $this->initProjectResource();
        $this->translater->addLoader('yml', $loader);
    }

    /**
     * initFrameworkResource
     * 
     * @return void
     */
    protected function initFrameworkResource()
    {
        $resourseDir = LOEYE_DIR . DIRECTORY_SEPARATOR . 'resource';
        foreach (new \FilesystemIterator($resourseDir, \FilesystemIterator::KEY_AS_FILENAME) as $key => $item) {
            if (!$item->isFile()) {
                continue;
            }
            $lpos = strpos($key, ".");
            $rpos = strrpos($key, ".");
            $lang = substr($key, $lpos + 1, $rpos - $lpos - 1);
            $this->translater->addResource('yml', $item->getRealPath(), $lang);
        }
    }


    /**
     * initProjectResource
     * 
     * @return void
     */
    protected function initProjectResource()
    {
        $resourseDir = PROJECT_LOCALE_DIR . DIRECTORY_SEPARATOR . $appConfig->getPropertyName();
        if (file_exists($resourseDir)) {
            foreach (new \FilesystemIterator($resourseDir, \FilesystemIterator::KEY_AS_FILENAME) as $key => $item) {
                if (!$item->isFile()) {
                    continue;
                }
                $lpos = strpos($key, ".");
                $rpos = strrpos($key, ".");
                $lang = substr($key, $lpos + 1, $rpos - $lpos - 1);
                $this->translater->addResource('yml', $item->getRealPath(), $lang);
            }
        }
    }


    /**
     * 
     * @return type
     */
    public function getLocale()
    {
        return $this->_locale;
    }


    /**
     * getString
     *
     * @param string $string source $string
     *
     * @return string
     */
    public function getString($string)
    {
        return $this->translater->trans($string);
    }


    /**
     * getReplacedString
     *
     * @param string       $key     key
     * @param string|array $search  search
     * @param string|array $replace replace
     * @param int          $count   count
     *
     * @return string
     */
    public function getReplacedString($key, $search, $replace, $count = null)
    {
        $string = $this->getString($key);
        return str_replace($search, $replace, $string, $count);
    }


    /**
     * getFormatString
     *
     * @param string $key  key
     * @param array  $args args
     *
     * @return string
     */
    public function getFormatString($key, $args)
    {
        $pattern = $this->getString($key);
        $result  = msgfmt_format_message($this->_locale, $pattern, $args);
        if ($result == false) {
            return $pattern;
        }
        return $result;
    }

}
