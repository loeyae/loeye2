<?php

/**
 * AbstractSampleParallelPlugin.php
 *
 * PHP version 7
 *
 * Licensed under the Apache License, Version 2.0 (the "License"),
 * see LICENSE for more details: http://www.apache.org/licenses/LICENSE-2.0.
 *
 * @category PHP
 * @package  LOEYE
 * @author   Zhang Yi <loeyae@gmail.com>
 * @version  Expression GitVersion is undefined on line 14, column 16 in Templates/Scripting/LoeyeNewParallelPlugin.php.
 * @link     https://github.com/loeyae/loeye.git
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
