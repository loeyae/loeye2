<?php

/**
 * <{$className}>.php
 *
 * @author Zhang Yi <loeyae@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache License
 * @version <{$smarty.now|date_format: "%Y-%m-%d %H:%M:%S"}>
 */
namespace <{$namespace}>;

use <{$fullClientName}>;
use loeye\base\Context;
use loeye\base\Utils;
use loeye\error\BusinessException;
use loeye\std\ParallelPlugin;

/**
 * Class <{$className}>
 *
 * @package <{$namespace}>
 */
abstract class <{$className}> extends ParallelPlugin
{

    /**
     * @var <{$clientName}> instance of <{$clientName}>
     */
    protected $client;

    /**
     * @var string  input data key
     */
    protected $inDataKey = '<{$className}>_input';

    /**
     * @var string output data key
     */
    protected $outDataKey = '<{$className}>_output';

    /**
     * @var string output error key
     */
    protected $outErrorsKey = '<{$className}>_errors';

    /**
     * @var mixed result
     */
    private $ret;

    /**
     * @param Context $context instance of Context
     * @param array $inputs plugin settings
     *
     * @return mixed|void
     * @throws BusinessException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function prepare(Context $context, array $inputs)
    {
        $this->client = new <{$clientName}>();
        Utils::addParallelClient($this->client, $context);
        $this->execute($context, $inputs);
    }

    /**
     * process
     *
     * @param Context $context instance of Context
     * @param array $inputs plugin settings
     * @return mixed|void
     */
    public function process(Context $context, array $inputs)
    {
        Utils::filterResultArray($this->ret, $data, $errors);
        Utils::setContextData($data, $context, $inputs, $this->outDataKey);
        if (!empty($errors)) {
            Utils::addErrors($errors, $context, $inputs, $this->outErrorsKey);
        }
    }

    /**
     * execute
     *
     * @param Context $context instance of Context
     * @param array $inputs plugin settings
     *
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    abstract protected function execute(Context $context, array $inputs): void;

}
