<?php

/**
 * TemplatePlugin.php
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

namespace loeye\plugin;

use loeye\base\Context;
use loeye\base\Exception;
use loeye\error\BusinessException;
use loeye\lib\ModuleParse;
use loeye\std\Plugin;
use loeye\web\Template;
use Smarty;
use SmartyException;
use const loeye\base\PROJECT_SUCCESS;

/**
 * TemplatePlugin
 *
 * @author   Zhang Yi <loeyae@gmail.com>
 */
class TemplatePlugin extends Plugin
{

    protected $allowedPluginsType = array(
        'function',
        'modifier',
        'block',
        'compiler',
        'prefilter',
        'postfilter',
        'outputfilter',
        'resource',
        'classes',
        'objects'
    );

    /**
     * process
     *
     * @param Context $context context
     * @param array $inputs inputs
     *
     * @return mixed|void
     * @throws SmartyException
     * @throws Exception
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function process(Context $context, array $inputs)
    {
        $template = $context->getTemplate();
        if (!($template instanceof Template)) {
            $template = new Template($context);
        }
        $this->registerPlugin($template, $inputs);
        $renderId = $context->getResponse()->getRenderId();
        $views    = $context->getModule()->getViews();
        if (empty($views)) {
            $view = [];
        } else {
            $view = $context->getModule()->getView($renderId);
        }
        $caching = $this->setCache($template, $inputs, $view);
        if ($caching === Smarty::CACHING_OFF) {
            $context->setTemplate($template);
            return PROJECT_SUCCESS;
        }
        $this->setCacheId($template, $context, $inputs);
        $context->setTemplate($template);
        if (isset($inputs['break']) && $inputs['break'] && $template->isCached($view['tpl'])) {
            return false;
        }
    }

    /**
     * registerPlugin
     *
     * @param Template $template template
     * @param array $inputs inputs
     *
     * @return void
     * @throws Exception
     * @throws SmartyException
     */
    protected function registerPlugin(Template $template, array $inputs): void
    {
        foreach ($this->allowedPluginsType as $type) {
            if (isset($inputs[$type])) {
                foreach ((array) $inputs[$type] as $key => $pluginSetting) {
                    switch ($type) {
                        case 'prefilter':
                        case 'postfilter':
                        case 'outputfilter':
                            $filter    = mb_substr($type, 0, -6);
                            $template->smarty()->registerFilter($filter, $pluginSetting);
                            break;
                        case 'function':
                        case 'modifier':
                        case 'block':
                        case 'compiler':
                            $cacheAble = false;
                            $attr      = array();
                            if (is_array($pluginSetting)) {
                                $count = count($pluginSetting);
                                if ($count === 1) {
                                    $callback = current($pluginSetting);
                                } else if ($count === 2) {
                                    [$callback, $cacheAble] = $pluginSetting;
                                } else if ($count >= 3) {
                                    [$callback, $cacheAble, $attr] = $pluginSetting;
                                } else {
                                    throw new BusinessException(BusinessException::INVALID_PLUGIN_SET_MSG,
                                            BusinessException::INVALID_PLUGIN_SET_CODE);
                                }
                            } else {
                                $callback = $pluginSetting;
                            }
                            $template->smarty()->registerPlugin($type, $key, $callback, $cacheAble, $attr);
                            break;
                        case 'resource':
                            if (is_numeric($key)) {
                                $key = $pluginSetting;
                            }
                            $template->smarty()->registerResource($key, new $pluginSetting());
                            break;
                        case 'classes':
                            if (is_numeric($key)) {
                                $key = $pluginSetting;
                            }
                            $template->smarty()->registerClass($key, $pluginSetting);
                            break;
                        case 'objects':
                            $allowed = array();
                            $args = true;
                            $block = [];
                            if (is_array($pluginSetting)) {
                                $count = count($pluginSetting);
                                if ($count === 1) {
                                    $object = current($pluginSetting);
                                } else if ($count === 2) {
                                    [$object, $allowed] = $pluginSetting;
                                } else if ($count === 3) {
                                    [$object, $allowed, $args] = $pluginSetting;
                                } else if ($count >= 4) {
                                    [$object, $allowed, $args, $block] = $pluginSetting;
                                } else {
                                    throw new BusinessException(BusinessException::INVALID_PLUGIN_SET_MSG,
                                            BusinessException::INVALID_PLUGIN_SET_CODE);
                                }
                            } else {
                                $object = $pluginSetting;
                            }
                            if (is_numeric($key)) {
                                $key = $object;
                            }
                            $o = new $object();
                            $template->smarty()->registerObject($key, $o, $allowed, $args, $block);
                            break;
                    }
                }
            }
        }
    }

    /**
     * setCacheId
     *
     * @param Template $template template
     * @param Context   $context  context
     * @param array                $inputs   inputs
     *
     * @return void
     */
    protected function setCacheId(
            Template $template, Context $context, array $inputs
    ): void
    {
        $cacheId = $context->getRequest()->getModuleId();
        if (isset($inputs['cache_key'])) {
            $cacheId .= '.' . $context->get($inputs['cache_key']);
            $template->setCacheId($cacheId);
        } else if (isset($inputs['match_id'])) {
            $cacheIdSetting = $inputs['match_id'];
            foreach ((array) $cacheIdSetting as $id => $match) {
                if (is_numeric($id)) {
                    $id    = $match;
                    $match = true;
                }
                if (ModuleParse::conditionResult($match, $context)) {
                    $cacheId .= '.' . $id;
                    break;
                }
            }
        }
        $template->setCacheId($cacheId);
    }

    /**
     * setCache
     *
     * @param Template $template template
     * @param array                $inputs   inputs
     * @param array                $view     view setting
     *
     * @return int
     */
    protected function setCache(Template $template, array $inputs, array $view): int
    {
        $caching  = Smarty::CACHING_OFF;
        $lifeTime = 0;
        if (defined('LOEYE_TEMPLATE_CACHE') && LOEYE_TEMPLATE_CACHE) {
            $caching = Smarty::CACHING_LIFETIME_CURRENT;
            if (is_numeric(LOEYE_TEMPLATE_CACHE)) {
                $lifeTime = LOEYE_TEMPLATE_CACHE;
            }
        }

        [$cache, $cacheLifeTime] = $this->parseCache($inputs, $view, $caching, $lifeTime);
        $template->setCache($cache);
        if ($cacheLifeTime === 0) {
            $template->setCacheLifeTime($cacheLifeTime);
        }
        return $cache;
    }

    /**
     * parseCache
     *
     * @param array $inputs        setting
     * @param array $view          setting
     * @param int   $caching       caching
     * @param int   $cacheLifeTime cache life time
     *
     * @return array
     */
    protected function parseCache(array $inputs, $view, $caching, $cacheLifeTime): array
    {
        $setting = (isset($view['cache']) ? $view : $inputs);
        if (isset($setting['cache'])) {
            if ($setting['cache']) {
                $caching = Smarty::CACHING_LIFETIME_CURRENT;
                if (is_numeric($setting['cache'])) {
                    $cacheLifeTime = (int)$setting['cache'];
                } else {
                    $cacheLifeTime = 0;
                }
            } else {
                $caching       = Smarty::CACHING_OFF;
                $cacheLifeTime = 0;
            }
        }
        return array($caching, $cacheLifeTime);
    }

}
