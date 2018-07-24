<?php

/**
 * Resource.php
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
 * Description of Resource
 *
 * @author   Zhang Yi <loeyae@gmail.com>
 */
class Resource
{

    const RESOURCE_TYPE_CSS = 'css';
    const RESOURCE_TYPE_JS = 'js';

    private $_resource = array();
    private $_type;

    /**
     * __construct
     *
     * @param string $type     type
     * @param type   $resource resource data
     *
     * @return avoid
     */
    public function __construct($type, $resource)
    {
        if (!is_string($type)) {
            throw new \loeye\base\Exception(
                    '无效的资源类型',
                    \loeye\base\Exception::INVALID_PARAMETER_CODE
            );
        }
        $this->_type = mb_strtolower($type);
        if (is_string($resource)) {
            $this->_resource[] = $resource;
        } else if (is_array($resource)) {
            foreach ($resource as $value) {
                if (!is_string($value)) {
                    throw new \loeye\base\Exception(
                            '无效的资源',
                            \loeye\base\Exception::INVALID_PARAMETER_CODE
                    );
                }
                $this->_resource[] = $value;
            }
        } else {
            throw new \loeye\base\Exception(
                    '无效的资源',
                    \loeye\base\Exception::INVALID_PARAMETER_CODE
            );
        }
    }

    /**
     * getType
     *
     * @return string
     */
    public function getType()
    {
        return $this->_type;
    }

    /**
     * toHeader
     *
     * @return string
     */
    public function toHeader()
    {
        $header = array();
        foreach ($this->_resource as $value) {
            if (self::RESOURCE_TYPE_CSS == $this->_type) {
                $header[] = "<{$value}>; rel=\"stylesheet\";type=\"text/css\"";
            } else if (self::RESOURCE_TYPE_JS == $this->_type) {
                $header[] = "<{$this->location}>; rel=\"script\";type=\"application/javascript\"";
            }
        }
        return implode(',', $header);
    }

    /**
     * toHtml
     *
     * @return string
     */
    public function toHtml()
    {
        $html = array();
        foreach ($this->_resource as $value) {
            if (self::RESOURCE_TYPE_CSS == $this->_type) {
                $html[] = "<link href=\"${value}\" rel=\"stylesheet\" type=\"text/css\" />";
            } else if (self::RESOURCE_TYPE_JS == $this->_type) {
                $html[] = "<script src=\"${value}\" type=\"text/javascript\"></script>";
            }
        }
        return implode(PHP_EOL, $html);
    }

}
