<?php

/**
 * ParallelPlugin.php
 *
 * PHP version 7
 *
 * Licensed under the Apache License, Version 2.0 (the "License"),
 * see LICENSE for more details: http://www.apache.org/licenses/LICENSE-2.0.
 *
 * @category PHP
 * @package  LOEYE
 * @author   Zhang Yi <loeyae@gmail.com>
 * @version  2018-07-23 22:44:28
 * @link     https://github.com/loeyae/loeye2.git
 */

namespace loeye\std;

use loeye\base\Context;
use const loeye\base\PROJECT_SUCCESS;

/**
 * ParallelPlugin
 *
 * @author   Zhang Yi <loeyae@gmail.com>
 */
abstract class ParallelPlugin extends Plugin
{

    /**
     * prepare
     *
     * @param Context $context Context
     * @param array $inputs array
     *
     * @return mixed
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function prepare(Context $context, array $inputs)
    {
        return PROJECT_SUCCESS;
    }

}
