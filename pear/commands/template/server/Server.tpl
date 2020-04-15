<?php

/**
 * <{$className}>.php
*
* @author Zhang Yi <loeyae@gmail.com>
* @license http://www.apache.org/licenses/LICENSE-2.0 Apache License
* @version <{$smarty.now|date_format:'%Y-%m-%d %H:%M:%S'}>
*/
namespace <{$namespace}>;

use loeye\database\Server;
use <{$fullEntityClass}>;

/**
 * <{$className}>
 *
 * @package <{$namespace}>
 */
class <{$className}> extends Server
{
    protected $entityClass = <{$entityClass}>::class;
}