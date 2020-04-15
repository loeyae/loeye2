<?php

/**
 * <{$className}>.php
 *
 * @author Zhang Yi <loeyae@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache License
 * @version <{$smarty.now|date_format:'%Y-%m-%d %H:%M:%S'}>
 */
namespace <{$namespace}>;

use <{$fullServerClass}>;
use loeye\base\Context;
use loeye\service\Handler;

/**
 * <{$className}>
 *
 * @package <{$namespace}>
 */
abstract class <{$className}> extends Handler
{

    /**
     * @var <{$serverClass}>
     */
    protected $server;

    /**
     * @inheritDoc
     */
    public function __construct(Context $context)
    {
        parent::__construct($context);
        $this->server = new <{$serverClass}>($context->getAppConfig());
    }

}