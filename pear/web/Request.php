<?php

/**
 * Request.php
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

namespace loeye\web;

use const loeye\base\RENDER_TYPE_HTML;
use const loeye\base\RENDER_TYPE_JSON;
use const loeye\base\RENDER_TYPE_SEGMENT;
use const loeye\base\RENDER_TYPE_XML;

/**
 * Description of Request
 *
 * @author   Zhang Yi <loeyae@gmail.com>
 */
class Request extends \loeye\std\Request
{

    protected $_allowedFormatType = array(
        RENDER_TYPE_SEGMENT,
        RENDER_TYPE_HTML,
        RENDER_TYPE_XML,
        RENDER_TYPE_JSON,
    );

}
