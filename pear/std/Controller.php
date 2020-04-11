<?php

/**
 * Controller.php
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
use loeye\base\Exception;
use const loeye\base\RENDER_TYPE_JSON;

/**
 * Controller
 *
 * @author   Zhang Yi <loeyae@gmail.com>
 */
abstract class Controller
{

    /**
     * Context instance
     *
     * @var Context
     */
    protected $context;
    public $view;

    public function __construct(Context $context)
    {
        $this->context = $context;
    }

    /**
     * prepare
     *
     * @return mixed
     */
    public function prepare()
    {
        return true;
    }

    /**
     * indexAction
     */
    abstract public function IndexAction();

    /**
     * render
     *
     * @param string $src view page
     *
     * @return void
     */
    protected function render($src): void
    {
        $this->view = ['src' => $src];
    }

    /**
     * template
     *
     * @param string $tpl template file
     * @param array $data data
     * @param mixed $cache cache setting
     * @param string $id cache id
     *
     * @return void
     */
    protected function template($tpl, $data = array(), $cache = 7200, $id = null): void
    {
        $this->view = ['tpl' => $tpl, 'data' => $data, 'cache' => $cache, 'id' => $id];
    }

    /**
     * output
     *
     * @param mixed $data data
     * @param string $format output format
     */
    protected function output($data, $format = RENDER_TYPE_JSON): void
    {
        $this->context->getResponse()->setFormat($format);
        $this->context->getResponse()->addOutput($data);
    }

    /**
     * redirectUrl
     *
     * @param string $redirectUrl redirect url
     *
     * @return void
     */
    public function redirectUrl($redirectUrl): void
    {
        $this->context->getResponse()->redirect($redirectUrl);
    }

    /**
     * redirect
     *
     * @param array $params parmeter
     * <p>
     * ex: ['controller' => 'main', 'action' => 'index']
     * </p>
     *
     * @return void
     * @throws Exception
     */
    public function redirect(array $params): void
    {
        $routeKey = $params[0];
        unset($params[0]);
        $url = $this->context->getUrlManager()->generate($routeKey, $params);
        $this->redirectUrl($url);
    }

}
