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
 * @version  2018-07-23 22:44:28
 * @link     https://github.com/loeyae/loeye2.git
 */

namespace loeye\web;

use loeye\error\BusinessException;

/**
 * Description of Resource
 *
 * @author   Zhang Yi <loeyae@gmail.com>
 */
class Resource
{

    public const RESOURCE_TYPE_CSS = 'css';
    public const RESOURCE_TYPE_JS = 'js';

    private $_resource = array();
    private $_type;

    /**
     * __construct
     *
     * @param string $type type
     * @param mixed $resource resource data
     *
     * @return void
     * @throws BusinessException
     */
    public function __construct($type, $resource)
    {
        if (!is_string($type)) {
            throw new BusinessException(
                    BusinessException::INVALID_PARAMETER_MSG,
                    BusinessException::INVALID_PARAMETER_CODE
            );
        }
        $this->_type = mb_strtolower($type);
        if (is_string($resource)) {
            $this->_resource[] = $resource;
        } else if (is_array($resource)) {
            foreach ($resource as $value) {
                if (!is_string($value)) {
                    throw new BusinessException(
                        BusinessException::INVALID_PARAMETER_MSG,
                        BusinessException::INVALID_PARAMETER_CODE
                    );
                }
                $this->_resource[] = $value;
            }
        } else {
            throw new BusinessException(
                BusinessException::INVALID_PARAMETER_MSG,
                BusinessException::INVALID_PARAMETER_CODE
            );
        }
    }

    /**
     * getType
     *
     * @return string
     */
    public function getType(): string
    {
        return $this->_type;
    }

    /**
     * toHeader
     *
     * @return string
     */
    public function toHeader(): string
    {
        $header = array();
        foreach ($this->_resource as $value) {
            if (self::RESOURCE_TYPE_CSS === $this->_type) {
                $header[] = "<{$value}>; rel=\"stylesheet\";type=\"text/css\"";
            } else if (self::RESOURCE_TYPE_JS === $this->_type) {
                $header[] = "<{$value}>; rel=\"script\";type=\"application/javascript\"";
            }
        }
        return implode(',', $header);
    }

    /**
     * toHtml
     *
     * @return string
     */
    public function toHtml(): string
    {
        $html = array();
        foreach ($this->_resource as $value) {
            if (self::RESOURCE_TYPE_CSS === $this->_type) {
                $html[] = "<link href=\"${value}\" rel=\"stylesheet\" type=\"text/css\" />";
            } else if (self::RESOURCE_TYPE_JS === $this->_type) {
                $html[] = "<script src=\"${value}\" type=\"text/javascript\"></script>";
            }
        }
        return implode(PHP_EOL, $html);
    }

}
