<?php

/**
 * Template.php
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

namespace loeye\web;

use loeye\base\Context;
use loeye\error\ResourceException;
use Smarty;
use SmartyException;

/**
 * Template
 *
 * @author   Zhang Yi <loeyae@gmail.com>
 */
class Template
{

    /**
     * template base dir name
     */
    public const B_D = 'smarty';

    /**
     * template cache dir name
     */
    public const C_D = 'cache';

    /**
     * smarty instance
     *
     * @var Smarty
     */
    protected $smarty;

    /**
     * cacheId
     *
     * @var string
     */
    public $cacheId;

    /**
     * __construct
     *
     * @param Context $context Context instance
     * @param string $propertyName property name
     *
     * @return void
     * @throws SmartyException
     */
    public function __construct(Context $context, $propertyName = null)
    {
        $this->smarty = new Smarty();
        $this->smarty->registerObject('context', $context);
        if (empty($propertyName)) {
            $propertyName = $context->getAppConfig()->getPropertyName();
        }
        $this->init($propertyName);
    }

    /**
     * init
     *
     * @param string $propertyName property name
     *
     * @return void
     */
    public function init($propertyName = null): void
    {
        $this->smarty->left_delimiter  = '<{';
        $this->smarty->right_delimiter = '}>';
        $this->smarty->setTemplateDir(PROJECT_VIEWS_DIR);
        $configDir                      = PROJECT_CONFIG_DIR . '/' . self::B_D;
        if (!empty($propertyName)) {
            $configDir .= '/' . $propertyName;
        }
        $this->smarty->setConfigDir($configDir);
        $this->smarty->addPluginsDir(PROJECT_DIR . '/lib/' . self::B_D);
        $compileDir = RUNTIME_DIR . '/' . self::B_D . '/compile';
        if (!empty($propertyName)) {
            $compileDir .= '/' . $propertyName;
        }
        $this->smarty->setCompileDir($compileDir);
        $cacheDir = RUNTIME_DIR . '/' . self::B_D . '/' . self::C_D;
        if (!empty($propertyName)) {
            $cacheDir .= '/' . $propertyName;
        }
        $this->smarty->setCacheDir($cacheDir);
        $this->smarty->use_sub_dirs = true;
    }

    /**
     * assign
     *
     * @param array $dataKey data key list
     * @param array $errorKey
     *
     * @return void
     */
    public function assign(array $dataKey = array(), $errorKey = array()): void
    {
        $context = $this->smarty->getRegisteredObject('context');
        if (!empty($dataKey)) {
            foreach ($dataKey as $key => $item) {
                $value = $context->get($item);
                if (is_numeric($key)) {
                    if (is_numeric($item)) {
                        $key = 'context_data_' . $item;
                    } else {
                        $key = $item;
                    }
                }
                $this->smarty->assign($key, $value);
                unset($value);
            }
        } else {
            $dataList = $context->getData();
            $this->smarty->assign('context_data', $dataList);
        }
        if (!empty($errorKey)) {
            foreach ($errorKey as $key => $item) {
                $value = $context->getErrors($item);
                if (is_numeric($key)) {
                    if (is_numeric($item)) {
                        $key = 'context_error_' . $item;
                    } else {
                        $key = $item;
                    }
                }
                $this->smarty->assign($key, $value);
                unset($value);
            }
        } else {
            $errors = $context->getErrors();
            $this->smarty->assign('context_errors', $errors);
        }
    }

    /**
     * setCacheId
     *
     * @param string $cacheId cache id
     *
     * @return void
     */
    public function setCacheId($cacheId): void
    {
        if (!empty($cacheId)) {
            $this->cacheId = str_replace('.', '_', $cacheId);
            $this->smarty->setCacheId($this->cacheId);
        }
    }

    /**
     * isCached
     *
     * @param string $tpl tpl file
     *
     * @return bool
     * @throws SmartyException
     */
    public function isCached($tpl): bool
    {
        $file = $this->formatFilePath($tpl);
        return $this->smarty->isCached($file);
    }

    /**
     * display
     *
     * @param string $tpl tpl file
     *
     * @return void
     * @throws ResourceException
     * @throws SmartyException
     */
    public function display($tpl): void
    {
        $file = $this->formatFilePath($tpl);
        if (!$this->smarty->templateExists($file)) {
            throw new ResourceException(ResourceException::FILE_NOT_FOUND_MSG, ResourceException::FILE_NOT_FOUND_CODE, ['file' => $file]);
        }
        $this->smarty->display($file);
    }

    /**
     * fetch
     *
     * @param string $tpl tpl file
     *
     * @return string
     * @throws ResourceException
     * @throws SmartyException
     */
    public function fetch($tpl): string
    {
        $file = $this->formatFilePath($tpl);
        if (!$this->smarty->templateExists($file)) {
            throw new ResourceException('template not found', ResourceException::FILE_NOT_FOUND_CODE);
        }
        return $this->smarty->fetch($file);
    }

    /**
     * setCache
     *
     * @param int $type type
     *
     * @return void
     */
    public function setCache($type = Smarty::CACHING_OFF): void
    {
        $this->smarty->setCaching($type);
    }

    /**
     * setCacheLifeTime
     *
     * @param int $lifeTime life time
     */
    public function setCacheLifeTime($lifeTime): void
    {
        if (is_numeric($lifeTime) && $lifeTime > 0) {
            $this->smarty->setCacheLifetime($lifeTime);
        }
    }

    /**
     * smarty
     *
     * @return Smarty
     */
    public function smarty(): Smarty
    {
        return $this->smarty;
    }

    /**
     * checkCompile
     *
     * @param bool $check is check
     */
    public function checkCompile($check = false): void
    {
        $this->smarty->compile_check = ($check !== false);
    }

    /**
     * formatFilePath
     *
     * @param string $file file
     *
     * @return string
     */
    protected function formatFilePath($file): string
    {
        if (is_file($file)) {
            return 'file:' . $file;
        }
        $dno = mb_strrpos($file, '.');
        return str_replace('.', '/', mb_substr($file, 0, $dno)) . mb_substr($file, $dno);
    }

}
