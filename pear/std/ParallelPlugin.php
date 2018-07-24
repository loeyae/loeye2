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
 * @version  GIT: $Id: Zhang Yi $
 * @link     https://github.com/loeyae/loeye.git
 */

namespace loeye\std;

/**
 * ParallelPlugin
 *
 * @author   Zhang Yi <loeyae@gmail.com>
 */
abstract class ParallelPlugin
{

    /**
     * prepare
     *
     * @param \loeye\base\Context $context  Context
     * @param array               $inputs  array
     *
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function prepare(\loeye\base\Context $context, array $inputs)
    {
        return \loeye\base\PROJECT_SUCCESS;
    }

    /**
     * process
     *
     * @param \loeye\base\Context $context  Context
     * @param array               $inputs  array
     *
     * @return void
     */
    abstract public function process(\loeye\base\Context $context, array $inputs);
}
