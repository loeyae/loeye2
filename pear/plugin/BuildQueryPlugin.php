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

use loeye\{base\Context,
    base\Factory,
    base\Utils,
    database\ExpressionFactory,
    database\QueryHelper,
    error\DAOException,
    error\ValidateError,
    std\Plugin,
    validate\Validation};
use Psr\Cache\InvalidArgumentException;
use ReflectionException;
use Throwable;
use const loeye\base\PROJECT_SUCCESS;

/**
 * BuildQueryPlugin
 *
 * @author Zhang Yi <loeyae@gmail.com>
 */
class BuildQueryPlugin implements Plugin {

    public const INPUT_ORIGIN = 100;

    protected $inDataKey     = 'BuildQueryPlugin_input';
    protected $outDataKey    = 'BuildQueryPlugin_output';
    protected $outErrorsKey  = 'BuildQueryPlugin_errors';
    protected $prefixKey     = 'prefix';
    protected $denyQueryKey  = 'deny';
    protected $allowedFields = 'fields';
    protected $validateKey   = 'validate';
    protected $criteriaKey   = 'criteria';


    protected $pageKey       = 'page';
    protected $hitsKey       = 'hits';
    protected $sortKey       = 'sort';
    protected $orderKey      = 'order';
    protected $groupKey      = 'group';
    protected $havingKey     = 'having';

    public const INPUT_TYPE          = 'type';
    public const DEFAULT_HITS        = 10;
    public const DEFAULT_PAGE        = 1;
    private $group = 'query';

    /**
     * process
     *
     * @param Context $context context
     * @param array $inputs inputs
     *
     * @return string|void
     * @throws InvalidArgumentException
     * @throws ReflectionException
     * @throws Throwable
     * @throws DAOException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function process(Context $context, array $inputs)
    {
        $prefix   = Utils::checkNotEmpty($inputs, $this->prefixKey);
        $method   = Utils::getData($inputs, self::INPUT_TYPE, null);
        $deny     = (bool) Utils::getData($inputs, $this->denyQueryKey, false);
        $criteria = (bool) Utils::getData($inputs, $this->criteriaKey, false);
        $validate = Utils::getData($inputs, $this->validateKey);
        $fields   = Utils::getData($inputs, $this->allowedFields);
        $data     = null;
        if (null === $method) {
            $data = Utils::getContextData($context, $inputs, $this->inDataKey);
        } else {
            $data = $this->getData($context, (int)$method);
        }
        $query = $this->parseQuery($context, $inputs, $prefix, $data);
        if ($data) {
            if ($deny) {
                $query = null;
            } else if ($criteria) {
                $expression = ExpressionFactory::create($data);
                if ($expression) {
                    if ($validate) {
                        try {
                            $validatedData = Validation::validate(ExpressionFactory::toFieldArray($expression), $validate, [], $this->group);
                        } catch (ValidateError $e) {
                            Utils::addErrors($e->getValidateMessage(), $context, $inputs,
                                $this->outErrorsKey);
                            return PROJECT_SUCCESS;
                        }
                        $filteredCompositeExpression = ExpressionFactory::filter($expression,
                            Utils::entity2array($context->db()->em(), $validatedData));
                        $query = ExpressionFactory::toCriteria($filteredCompositeExpression);
                    } else {
                        $query = ExpressionFactory::toCriteria($expression);
                    }
                } else {
                    $query = null;
                }
            } else {
                if ($fields) {
                    $fields = array_fill_keys($fields, null);
                    $query = array_intersect_key($data, $fields);
                }
                if ($validate) {
                    try {
                        $entity = Validation::validate($query, $validate, [], $this->group);
                        $validated = Utils::entity2array($context->db()->em(), $entity);
                        $query = array_filter($validated, static function ($item) {
                            return $item !== null;
                        });
                    } catch (ValidateError $e) {
                        Utils::addErrors($e->getValidateMessage(), $context, $inputs,
                            $this->outErrorsKey);
                        return PROJECT_SUCCESS;
                    }
                }
            }
        }
        $context->set($prefix . '_input', $query);

    }

    /**
     * @param Context $context
     * @param array $inputs
     * @param $prefix
     * @param $data
     * @return array
     */
    protected function parseQuery(Context $context, array $inputs, $prefix, $data): array
    {
        $pageKey  = Utils::getData($inputs, $this->pageKey, QueryHelper::PAGE_NAME);
        $hitsKey  = Utils::getData($inputs, $this->hitsKey, QueryHelper::HITS_NAME);
        $sortKey  = Utils::getData($inputs, $this->sortKey, QueryHelper::SORT_NAME);
        $orderKey = Utils::getData($inputs, $this->orderKey, QueryHelper::ORDER_NAME);
        $group    = Utils::getData($inputs, $this->groupKey);
        $having   = Utils::getData($inputs, $this->havingKey);
        $queryHelper = QueryHelper::init()->setPageKey($pageKey)->setHitsKey($hitsKey)
            ->setSortKey($sortKey)->setOrderKey($orderKey)->setGroup($group)->setHaving($having)
            ->setGroupKey('?')->setHavingKey('?')
            ->setDefaultHits(self::DEFAULT_HITS)->setDefaultPage(self::DEFAULT_PAGE);
        $data = $queryHelper->parseQuery($data);
        $context->set($prefix . '_start', $queryHelper->getStart());
        $context->set($prefix . '_offset', $queryHelper->getOffset());
        if ($orderBy = $queryHelper->getOrderBy()) {
            $context->set($prefix . '_orderBy', $orderBy);
        }
        if ($groupBy = $queryHelper->getGroupBy()) {
            $context->set($prefix . '_groupBy', $groupBy);
        }
        if ($having = $queryHelper->getHaving()) {
            $context->set($prefix . '_having', $having);
        }
        return $data;
    }


    /**
     * @param Context $context
     * @param $method
     * @return array|null
     */
    private function getData(Context $context, $method): ?array
    {
        if ($method === INPUT_GET) {
            return $context->getRequest()->query->all()  ?? [];
        }
        if ($method === INPUT_POST) {
            return $context->getRequest()->request->all() ?? [];
        }
        if ($method === self::INPUT_ORIGIN) {
            return json_decode($context->getRequest()->getContent(), true)  ?: [];
        }
        return $context->getRequest()->getParameter() ?? [];
    }

}
