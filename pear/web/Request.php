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
 * @version  GIT: $Id: Zhang Yi $
 * @link     https://github.com/loeyae/loeye.git
 */

namespace loeye\web;

/**
 * Description of Request
 *
 * @author   Zhang Yi <loeyae@gmail.com>
 */
class Request extends \loeye\std\Request
{

    protected $allowedFormatType = array(
        \loeye\base\RENDER_TYPE_SEGMENT,
        \loeye\base\RENDER_TYPE_HTML,
        \loeye\base\RENDER_TYPE_XML,
        \loeye\base\RENDER_TYPE_JSON,
    );

}
