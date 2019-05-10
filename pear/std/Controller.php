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
     * @var \loeye\base\Context
     */
    protected $context;
    public $view;

    public function __construct(\loeye\base\Context $context)
    {
        $this->context = $context;
    }

    /**
     * prepare
     *
     * @return void
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
    protected function render($src)
    {
        $this->view = ['src' => $src];
    }

    /**
     * template
     *
     * @param string $tpl   template file
     * @param array  $data  data
     * @param mixed  $cache cache setting
     * @param string $id    cache id
     *
     * @return void
     */
    protected function template($tpl, $data = array(), $cache = 7200, $id = null)
    {
        $this->view = ['tpl' => $tpl, 'data' => $data, 'cache' => $cache, 'id' => $id];
    }

    /**
     * oupput
     *
     * @param mixed  $data   data
     * @param string $format output format
     */
    protected function oupput($data, $format = RENDER_TYPE_JSON)
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
    public function redirectUrl($redirectUrl)
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
     */
    public function redirect(array $params)
    {
        if (isset($params[0])) {

        }
        $url = $this->context->getUrlManager()->generate($params);
        return $this->redirectUrl($url);
    }

}
