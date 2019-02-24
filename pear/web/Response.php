<?php

/**
 * Response.php
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
 * Response
 *
 * @author   Zhang Yi <loeyae@gmail.com>
 */
class Response extends \loeye\std\Response
{

    const DEFAULT_RENDER_ID = 'default';
    const DEFAULT_MOBILE_RENDER_ID = 'mobile';

    private $_renderId = self::DEFAULT_RENDER_ID;
    private $_resource = array();
    private $_htmlHead = array();
    private $_redirectUrl;

    /**
     * __construct
     */
    public function __construct()
    {
        $this->header = array();
    }

    /**
     * offsetExists
     *
     * @param mixed $offset offset
     *
     * @return boolean
     */
    public function offsetExists($offset)
    {
        $methodList = array(
            'getHeaders',
            'getHtmlHead',
            'getOutput',
            'getRenderId',
            'getResource',
            'getResourceTypes',
            'getRedirectUrl',
        );
        if (in_array($offset, $methodList) || in_array($offset, $propertyList)) {
            return true;
        }
        return false;
    }

    /**
     * offsetGet
     *
     * @param mixed $offset offset
     *
     * @return mixed
     */
    public function offsetGet($offset)
    {
        switch ($offset) {
            case 'getHeaders':
            case 'getHtmlHead':
            case 'getOutput':
            case 'getRenderId':
            case 'getResource':
            case 'getResourceTypes':
            case 'getRedirectUrl':
                return $this->$offset();
            default :
                return null;
        }
    }

    /**
     * offsetSet
     *
     * @param mixed $offset offset
     * @param mixed $value  value
     *
     * @return void
     */
    public function offsetSet($offset, $value)
    {
        switch ($offset) {
            case 'addHtmlHead':
            case 'setRenderId':
            case 'addResource':
            case 'setRedirectUrl':
                $this->$offset($value);
            default :
                break;
        }
    }

    /**
     * offsetUnset
     *
     * @param mixed $offset offset
     *
     * @return void
     */
    public function offsetUnset($offset)
    {
        return;
    }
    /**
     * addHtmlHead
     *
     * @param string $data data
     *
     * @return void
     */
    public function addHtmlHead($data)
    {
        $this->_htmlHead[] = $data;
    }

    /**
     * getHtmlHead
     *
     * @return array()
     */
    public function getHtmlHead()
    {
        return $this->_htmlHead;
    }

    /**
     * getOutput
     *
     * @return array()
     */
    public function getOutput()
    {
        return $this->output;
    }

    /**
     * flush
     *
     * @return void
     */
    public function flush()
    {
        $this->output = array();
    }

    /**
     * setRenderId
     *
     * @param string $renderId render id
     *
     * @return void;
     */
    public function setRenderId($renderId)
    {
        $this->_renderId = $renderId;
    }

    /**
     * getRenderId
     *
     * @return string
     */
    public function getRenderId()
    {
        return $this->_renderId;
    }

    /**
     * addResource
     *
     * @param \LOEYE\Resource $resource resource
     *
     * @return void
     */
    public function addResource(Resource $resource)
    {
        $type                   = $resource->getType();
        $this->_resource[$type] = $resource;
    }

    /**
     * getResource
     *
     * @param string $type type
     *
     * @return Object
     */
    public function getResource($type = null)
    {
        if (isset($type)) {
            return isset($this->_resource[$type]) ? $this->_resource[$type] : null;
        }
        return $this->_resource;
    }

    /**
     * getResourceTypes
     *
     * @return array
     */
    public function getResourceTypes()
    {
        return array_keys($this->_resource);
    }

    /**
     * setRedirectUrl
     *
     * @param string $url url
     *
     * @return void
     */
    public function setRedirectUrl($url)
    {
        $this->_redirectUrl = $url;
    }

    /**
     * getRedirectUrl
     *
     * @return string
     */
    public function getRedirectUrl()
    {
        return $this->_redirectUrl;
    }

    /**
     * redirect
     *
     * @param string $redirectUrl string
     *
     * @return void
     * @SuppressWarnings(PHPMD.ExitExpression)
     */
    public function redirect($redirectUrl = null)
    {
        if (empty($redirectUrl)) {
            $redirectUrl = $this->_redirectUrl;
        }
        if (!empty($redirectUrl)) {
            header("Location:$redirectUrl");
            exit;
        }
        throw new \loeye\base\Exception(
                '无效的跳转链接',
                \loeye\base\Exception::INVALID_PARAMETER_CODE
        );
    }

}
