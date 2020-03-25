<?php

/**
 * BuildQueryPlugin.php
 * 
 * Licensed under the Apache License, Version 2.0 (the "License"),
 * see LICENSE for more details: http://www.apache.org/licenses/LICENSE-2.0.
 * 
 * @author  Zhang Yi <loeyae@gmail.com>
 * @version 2020年3月25日 下午7:50:03
 */

namespace loeye\plugin;

use \loeye\{
    base\Context,
    base\Utils,
    std\Plugin
};

/**
 * BuildQueryPlugin
 *
 * @author Zhang Yi <loeyae@gmail.com>
 */
class BuildQueryPlugin extends Plugin {

    protected $inDataKey    = 'BuildQueryPlugin_input';
    protected $outDataKey   = 'BuildQueryPlugin_output';
    protected $outErrorsKey = 'BuildQueryPlugin_errors';
    protected $prefixKey    = 'prefix';
    protected $pageKey      = 'page';
    protected $hitsKey      = 'hits';
    protected $sortKey      = 'sort';
    protected $orderKey     = 'order';
    protected $groupKey     = 'group';
    protected $havingKey    = 'having';

    const PAGE_NAME    = 'p';
    const HITS_NAME    = 'h';
    const ORDER_NAME   = 'o';
    const SORT_NAME    = 's';
    const INPUT_TYPE   = 'type';
    const DEFAULT_HITS = 10;
    const DEFAULT_PAGE = 1;
    const ORDER_ASC    = 'ASC';
    const ORDER_DESC   = 'DESC';

    /**
     * process
     *
     * @param Context $context context
     * @param array               $inputs  inputs
     *
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function process(Context $context, array $inputs)
    {
        $prefix   = Utils::checkNotEmpty($inputs, $this->prefixKey);
        $pageKey  = Utils::getData($inputs, $this->pageKey, self::PAGE_NAME);
        $hitsKey  = Utils::getData($inputs, $this->hitsKey, self::HITS_NAME);
        $sortKey  = Utils::getData($inputs, $this->sortKey, self::SORT_NAME);
        $orderKey = Utils::getData($inputs, $this->orderKey, self::ORDER_NAME);
        $group    = Utils::getData($inputs, $this->groupKey);
        $having   = Utils::getData($inputs, $this->havingKey);
        $method   = Utils::getData($inputs, self::INPUT_TYPE, null);
        $data     = null;
        if (null == $method) {
            $data = Utils::getContextData($context, $inputs, $this->inDataKey);
        } else {
            $data = filter_input_array($method);
        }
        $page  = self::DEFAULT_PAGE;
        $hits  = self::DEFAULT_HITS;
        $sort  = null;
        $order = null;
        if (null != $data) {
            $page  = $this->pop($data, $pageKey, self::DEFAULT_PAGE);
            $hits  = $this->pop($data, $hitsKey, self::DEFAULT_HITS);
            $order = $this->pop($data, $orderKey);
            $sort  = $this->pop($data, $sortKey);
        }
        $query = $data;
        if (!is_int($page) || is_int($hits)) {
            $context->addErrors($this->outErrorsKey, new \loeye\error\RequestParameterException());
            return false;
        }
        $context->set($prefix . '_query', $query);
        $context->set($prefix . '_start', ($page - 1) * $hits);
        $context->set($prefix . '_offset', $hits);
        if ($sort) {
            $context->set($prefix . '_orderBy', $sort . ' ' . ($order > 1 ? self::ORDER_ASC : self::ORDER_DESC));
        }
        if ($group) {
            $context->set($prefix . '_groupBy', $group);
        }
        if ($having) {
            $context->set($prefix . '_having', $having);
        }
    }

    /**
     * pop value from array
     * 
     * @param array $data    data
     * @param mixed $key     key
     * @param mixed $default default
     * 
     * @return mixed
     */
    protected function pop(array &$data, $key, $default = null)
    {
        $value = $default;
        if (isset($data[$key])) {
            $value = $data[$key];
            unset($data[$key]);
        }
        return $value;
    }

}
