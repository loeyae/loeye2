<?php

/**
 * AbstractServer.php
 *
 * Licensed under the Apache License, Version 2.0 (the "License"),
 * see LICENSE for more details: http://www.apache.org/licenses/LICENSE-2.0.
 *
 * @author  Zhang Yi <loeyae@gmail.com>
 * @version 2019-02-25 17:39:00
 */
namespace app\services\server;

/**
 * AbstractServer
 *
 * @author   Zhang Yi <loeyae@gmail.com>
 */
abstract class AbstractServer
{

    /**
     * config
     *
     * @var \loeye\base\AppConfig \loeye\base\LoeyeAppConfig instance
     */
    protected $config;

    /**
     * __construct
     *
     * @param \loeye\base\AppConfig $appConfig app config
     *
     * @return void
     */
    public function __construct(\loeye\base\AppConfig $appConfig)
    {
        $this->config = $appConfig;
        $this->init();
    }

    /**
     * init
     *
     * @return void
     */
    abstract protected function init();
}
