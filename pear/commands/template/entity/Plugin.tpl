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
use loeye\base\Utils;

/**
 * <{$className}>
 *
 * @package <{$namespace}>
 */
class <{$className}> extends <{$abstractClassName}>
{

    protected $inDataKey = '<{$className}>_input';
    protected $outDataKey = '<{$className}>_output';
    protected $outErrorsKey = '<{$className}>_errors';


    /**
     * execute
     *
     * @param Context $context context
     * @param array $inputs inputs
     * @param string $type db setting id
     *
     * @return <{$returnType}>
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function execute(Context $context, array $inputs, $type)
    {
<paramsStatement>
        return $this->server-><method>(<params>);
    }

}