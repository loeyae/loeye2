<?php

/**
 * QueryHelper.php
 *
 * @author Zhang Yi <loeyae@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache License
 * @version 2020/5/11 20:48
 */

namespace loeye\database;


use loeye\base\Context;
use loeye\base\Factory;
use loeye\base\Utils;
use React\Stream\Util;

class QueryHelper
{
    private $page;
    private $hits;
    private $group;
    private $having;

    private $defaultPage;
    private $defaultHits;


    protected $pageKey;
    protected $hitsKey;
    protected $sortKey;
    protected $orderKey;

    public const PAGE_NAME   = 'p';
    public const HITS_NAME   = 'h';
    public const ORDER_NAME  = 'o';
    public const SORT_NAME   = 's';
    public const ORDER_ASC   = 'ASC';
    public const ORDER_DESC  = 'DESC';
    public const GROUP_NAME  = 'group';
    public const HAVING_NAME = 'having';
    /**
     * @var array
     */
    private $orderBy;
    /**
     * @var array
     */
    private $groupBy;
    private $groupKey;
    private $havingKey;

    private function __construct()
    {
    }

    /**
     * @return QueryHelper
     */
    public static function init(): QueryHelper
    {
        static $instance;
        if (!$instance) {
            $instance = new self();
        }
        return $instance;
    }

    /**
     * @param mixed $defaultPage
     * @return QueryHelper
     */
    public function setDefaultPage($defaultPage): QueryHelper
    {
        $this->defaultPage = $defaultPage;
        return $this;
    }

    /**
     * @param mixed $defaultHits
     * @return QueryHelper
     */
    public function setDefaultHits($defaultHits): QueryHelper
    {
        $this->defaultHits = $defaultHits;
        return $this;
    }

    /**
     * @param mixed $page
     */
    public function setPage($page): void
    {
        $p = (int)$page;
        if ($p < 1) {
            $p = 1;
        }
        $this->page = $p;
    }

    /**
     * @return mixed
     */
    public function getPage()
    {
        return $this->page;
    }

    /**
     * @param mixed $hits
     */
    public function setHits($hits): void
    {
        $h = (int)$hits;
        if ($h < 0) {
            $h = 0;
        }
        $this->hits = $h;
    }

    /**
     * @return mixed
     */
    public function getHits()
    {
        return $this->hits;
    }

    /**
     * @param string $pageKey
     * @return QueryHelper
     */
    public function setPageKey(string $pageKey): QueryHelper
    {
        $this->pageKey = $pageKey;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getPageKey()
    {
        return $this->pageKey ?? self::PAGE_NAME;
    }

    /**
     * @param string $hitsKey
     * @return QueryHelper
     */
    public function setHitsKey(string $hitsKey): QueryHelper
    {
        $this->hitsKey = $hitsKey;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getHitsKey()
    {
        return $this->hitsKey ?? self::HITS_NAME;
    }

    /**
     * @param string $sortKey
     * @return QueryHelper
     */
    public function setSortKey(string $sortKey): QueryHelper
    {
        $this->sortKey = $sortKey;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getSortKey()
    {
        return $this->sortKey ?? self::SORT_NAME;
    }

    /**
     * @param string $orderKey
     * @return QueryHelper
     */
    public function setOrderKey(string $orderKey): QueryHelper
    {
        $this->orderKey = $orderKey;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getOrderKey()
    {
        return $this->orderKey ?? self::ORDER_NAME;
    }

    /**
     * @param string $group
     * @return QueryHelper
     */
    public function setGroup(string $group = null): QueryHelper
    {
        $this->group = $group;
        return $this;
    }

    /**
     * @param string $having
     * @return QueryHelper
     */
    public function setHaving(string $having = null): QueryHelper
    {
        $this->having = $having;
        return $this;
    }

    /**
     * @param $data
     * @return array
     */
    public function parseQuery($data): array
    {
        $data = $this->parsePaging($data);
        $data = $this->parseAdvanced($data);
        return $data;
    }

    /**
     * @param $data
     * @return array
     */
    protected function parsePaging($data): array
    {
        $page  = $this->defaultPage;
        $hits  = $this->defaultHits;
        if ($data) {
            [$data, $page] = Utils::arrayPop($data, $this->getPageKey(), $page);
            [$data, $hits] = Utils::arrayPop($data, $this->getHitsKey(), $hits);
        }
        $this->setPage($page);
        $this->setHits($hits);
        return $data;
    }

    /**
     * @param $data
     * @return array
     */
    protected function parseAdvanced($data): array
    {
        $sort  = null;
        $order = null;
        if ($data) {
            [$data, $order] = Utils::arrayPop($data, $this->getOrderKey());
            [$data, $sort]  = Utils::arrayPop($data, $this->getSortKey());
            [$data, $group] = Utils::arrayPop($data, $this->getGroupKey());
            if ($group) {
                $this->setGroup($group);
            }
            [$data, $having] = Utils::arrayPop($data, $this->getHavingKey());
            if ($having) {
                $this->setHaving($having);
            }
        }
        if ($sort) {
            $sortArray = (array)$sort;
            $orderArray = (array)$order;
            $sortCount = count($sortArray);
            $orderArray = array_slice(array_pad($orderArray, $sortCount, 0), 0, $sortCount);
            $orderBy = array_combine(array_map(static function($item){
                return htmlentities($item);
            }, $sortArray), array_map(static function($item){
                return $item > 0 ? self::ORDER_ASC :self::ORDER_DESC;
            }, $orderArray));
            $this->orderBy = $orderBy;
        }
        if ($this->group) {
            $this->groupBy = array_map(static function($item){
                return htmlentities($item);
            }, (array)$this->group);
        }
        return $data;
    }

    /**
     * @return mixed
     */
    public function getGroupBy()
    {
        return $this->groupBy;
    }

    /**
     * @return mixed
     */
    public function getOrderBy()
    {
        return $this->orderBy;
    }

    /**
     * @return mixed
     */
    public function getHaving()
    {
        return $this->having;
    }


    /**
     * @return mixed
     */
    public function getStart()
    {
        return ($this->page - 1) * $this->hits;
    }

    /**
     * @return mixed
     */
    public function getOffset()
    {
        return $this->hits ?? $this->defaultHits;
    }

    /**
     * @param mixed $groupKey
     * @return QueryHelper
     */
    public function setGroupKey($groupKey): QueryHelper
    {
        $this->groupKey = $groupKey;
        return $this;
    }

    /**
     * @return string
     */
    private function getGroupKey(): string
    {
        return $this->groupKey ?? self::GROUP_NAME;
    }

    /**
     * @param mixed $havingKey
     * @return QueryHelper
     */
    public function setHavingKey($havingKey): QueryHelper
    {
        $this->havingKey = $havingKey;
        return $this;
    }

    /**
     * @return string
     */
    private function getHavingKey(): string
    {
        return $this->havingKey ?? self::HAVING_NAME;
    }

}