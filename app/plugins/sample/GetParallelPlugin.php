<?php

/**
 * GetParallelPlugin.php
 *
 * Licensed under the Apache License, Version 2.0 (the "License"),
 * see LICENSE for more details: http://www.apache.org/licenses/LICENSE-2.0.
 *
 * @author  Zhang Yi <loeyae@gmail.com>
 * @version 2019-02-25 17:39:00
 */

namespace app\plugins\sample;

/**
 * GetParallelPlugin
 *
 * @author   Zhang Yi <loeyae@gmail.com>
 */
class GetParallelPlugin extends AbstractSampleParallelPlugin
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
        $id = \loeye\base\Utils::checkNotEmptyContextData($context, $inputs, $this->inDataKey);
        $this->client->getUser($id, $this->ret);
    }

}
