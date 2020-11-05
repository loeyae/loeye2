<?php

/**
 * <{$className}>.php
 *
 * @author Zhang Yi <loeyae@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache License
 * @version <{$smarty.now|date_format:"%Y-%m-%d %H:%M:%S"}>
 */
namespace <{$namespace}>;

use loeye\base\Context;
use loeye\base\Utils;

/**
 * Class <{$className}>
 *
 * @package <{$namespace}>
 */
class <{$className}> extends <{$abstractClassName}>
{

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
     * @param Context $context
     * @param array $inputs
     *
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function execute(Context $context, array $inputs): void
    {
<{$parameterStatement}>
        $this->client-><{$method}>(<{$parameter}>, $this->ret);
    }
}
