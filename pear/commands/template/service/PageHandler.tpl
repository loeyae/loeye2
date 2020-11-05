<?php

/**
 * <{$className}>.php
 *
 * @author Zhang Yi <loeyae@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache License
 * @version <{$smarty.now|date_format:'%Y-%m-%d %H:%M:%S'}>
 */
namespace <{$namespace}>;

use loeye\base\Logger;
use Throwable;
<{$useStatement}>

/**
 * <{$className}>
 *
 * @package <{$namespace}>
 */
class <{$className}> extends <{$abstractClassName}>
{

<{$propertyStatement}>
    /**
<{$methodDoc}>
     */
    protected function process($req)
    {
<{$parameterStatement}>
        try {
            return $this->server-><{$method}>(<{$parameter}>);
        } catch (Throwable $e) {
            Logger::exception($e);
        }
        return [];
    }
}