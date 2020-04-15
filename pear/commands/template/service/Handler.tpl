<?php

/**
 * <{$className}>.php
 *
 * @author Zhang Yi <loeyae@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache License
 * @version <{$smarty.now|date_format:'%Y-%m-%d %H:%M:%S'}>
 */
namespace <{$namespace}>;

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
        return $this->server-><{$method}>(<{$parameter}>);
    }
}