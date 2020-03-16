<?php

/**
 * Translator.php
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
 * Translator
 *
 * @author   Zhang Yi <loeyae@gmail.com>
 */
class Translator
{

    private $_locale = "zh_CN";
    private $_domain = "lang";

    /**
     *
     * @var \Symfony\Component\Translation\Translator
     */
    protected $translator;

    /**
     * __construct
     *
     * @param \loeye\base\AppConfig $appConfig AppConfig instance
     */
    public function __construct(AppConfig $appConfig = null)
    {
        if ($appConfig) {
            $this->_locale = $appConfig->getLocale();
            $this->_domain = $appConfig->getSetting('locale.basename', $this->_domain);
        }
        $this->translator = new I18n\Translator($this->_locale);
        $loader           = new I18n\Loader\YamlFileLoader();
        $this->initFrameworkResource();
        $this->initProjectResource($appConfig);
        $this->translator->addLoader('yml', $loader);
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
            $args   = explode('.', $key);
            $lang   = $args[1];
            $domain = $this->_domain;
            if (count($args) > 3) {
                $domain = $args[2];
            }
            $this->translator->addResource('yml', $item->getRealPath(), $lang, $domain);
        }
    }

    /**
     * initProjectResource
     *
     * @param \loeye\base\AppConfig $appConfig AppConfig instance
     *
     * @return void
     */
    protected function initProjectResource(AppConfig $appConfig = null)
    {
        if (!$appConfig) {
            return ;
        }
        $resourseDir = PROJECT_LOCALE_DIR . DIRECTORY_SEPARATOR . $appConfig->getPropertyName();
                
        if (file_exists($resourseDir)) {
            foreach (new \FilesystemIterator($resourseDir, \FilesystemIterator::KEY_AS_FILENAME) as $key => $item) {
                if (!$item->isFile()) {
                    continue;
                }
                $args   = explode('.', $key);
                $lang   = $args[1];
                $domain = $this->_domain;
                if (count($args) > 3) {
                    $domain = $args[2];
                }
                $this->translator->addResource('yml', $item->getRealPath(), $lang, $domain);
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
    public function getString($string, array $parameters = [], $domain = null, $locale = null)
    {
        $domain ?? $domain = $this->_domain;
        $locale ?? $locale = $this->_locale;
        return $this->translator->trans($string, $parameters, $domain, $locale);
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
        $string = $this->getString($key, [], $this->_domain, $this->_locale);
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
        $pattern = $this->getString($key, [], $this->_domain, $this->_locale);
        $result  = msgfmt_format_message($this->_locale, $pattern, $args);
        if ($result == false) {
            return $pattern;
        }
        return $result;
    }
    
    /**
     * getTranslator
     * 
     * @return \Symfony\Component\Translation\Translator
     */
    public function getTranslator()
    {
        return $this->translator;
    }

}
