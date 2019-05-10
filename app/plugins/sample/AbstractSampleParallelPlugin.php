<?php

/**
 * AbstractSampleParallelPlugin.php
 *
 * Licensed under the Apache License, Version 2.0 (the "License"),
 * see LICENSE for more details: http://www.apache.org/licenses/LICENSE-2.0.
 *
 * @author  Zhang Yi <loeyae@gmail.com>
 * @version 2019-02-25 17:39:00
 */

namespace app\plugins\sample;

use \loeye\std\ParallelPlugin;

/**
 * AbstractSampleParallelPlugin
 *
 * @author   Zhang Yi <loeyae@gmail.com>
 */
abstract class AbstractSampleParallelPlugin extends ParallelPlugin
{
    protected $ret;

    /**
     *
     * @var \app\services\client\SampleClient
     */
    protected $client;

    protected $outDataKey = 'sample_data';

    protected $outErrorsKey = 'sample_errors';

    /**
     * prepare
     *
     * @param \loeye\base\Context $context context
     * @param array               $inputs  inputs
     *
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function prepare(\loeye\base\Context $context, array $inputs)
    {
        $this->client = new \app\services\client\SampleClient();
        \loeye\base\Utils::addParallelClient($this->client, $context);
        $this->excute($context, $inputs);
    }

    /**
     * process
     *
     * @param \loeye\base\Context $context context
     * @param array               $inputs  inputs
     *
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function process(\loeye\base\Context $context, array $inputs)
    {
        $data = array();
        $errors = array();
        \loeye\base\Utils::filterResultArray($this->ret, $data, $errors);
        \loeye\base\Utils::setContextData($data, $context, $inputs, $this->outDataKey);
        if (!empty($errors)) {
            \loeye\base\Utils::addErrors($errors, $context, $inputs, $this->outErrorsKey);
        }
    }

    /**
     * excute
     *
     * @param \loeye\base\Context $context context
     * @param array               $inputs  inputs
     *
     * @return void
     */
    abstract protected function excute(\loeye\base\Context $context, array $inputs);


}
