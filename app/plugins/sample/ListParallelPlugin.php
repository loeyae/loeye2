<?php

/**
 * ListParallelPlugin.php
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

/**
 * ListParallelPlugin
 *
 * @author   Zhang Yi <loeyae@gmail.com>
 */
class ListParallelPlugin extends AbstractSampleParallelPlugin
{

    protected $inDataKey = 'sample_get_id';

    protected $outDataKey = 'sample_get_result';

    protected $outErrorsKey = 'sample_get_errors';

    /**
     * excute
     *
     * @param \loeye\base\Context $context context
     * @param array               $inputs  inputs
     *
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function excute(\loeye\base\Context $context, array $inputs)
    {
        $id = \loeye\base\Utils::getContextData($context, $inputs, $this->inDataKey);
        $this->client->listUser($id, $this->ret);
    }

}
