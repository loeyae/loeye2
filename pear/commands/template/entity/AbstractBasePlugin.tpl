<?php

/**
 * <{$className}>.php
*
* @author Zhang Yi <loeyae@gmail.com>
* @license http://www.apache.org/licenses/LICENSE-2.0 Apache License
* @version <{$smarty.now|date_format:'%Y-%m-%d %H:%M:%S'}>
*/
namespace <{$namespace}>;

use loeye\base\Context;
use loeye\std\Plugin;
use loeye\base\Utils;
use <{$fullServerClass}>;

/**
 * <{$className}>
 *
 * This class was generated by the loeye2. Add your own custom
 * server methods below.
 */
abstract class <{$className}> implements Plugin
{

    protected $inDataKey = '<{$className}>_input';
    protected $outDataKey = '<{$className}>_output';
    protected $outErrorsKey = '<{$className}>_errors';

    /**
     *
     * @var <{$serverClass}>
     */
    protected $server;

    protected $dbId = 'default';

    /**
     * process
     *
     * @param Context $context context
     * @param array $inputs  inputs
     *
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function process(Context $context, array $inputs)
    {
        $type = Utils::getContextData($context, $inputs, $this->dbId);
        $this->server = new <{$serverClass}>($context->getAppConfig(), $type);
        $result = $this->execute($context, $inputs, $type);
        Utils::filterResult($result, $data, $error);
        Utils::setContextData($data, $context, $inputs, $this->outDataKey);
        if ($error) {
            Utils::addErrors($error, $context, $inputs, $this->outErrorsKey);
        }
    }

    abstract protected function execute(Context $context, array $inputs, $type);

}