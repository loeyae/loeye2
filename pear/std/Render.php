<?php

/**
 * Render.php
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
 * interface Render
 *
 * @author   Zhang Yi <loeyae@gmail.com>
 */
interface Render
{

    /**
     * header
     *
     * @param \loeye\std\Response $response response
     *
     * @return void
     */
    public function header(Response $response);

    /**
     * oupput
     *
     * @param \loeye\std\Response $response response
     *
     * @return void
     */
    public function output(Response $response);
}
