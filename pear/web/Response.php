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
 * @version  2018-07-23 22:44:28
 * @link     https://github.com/loeyae/loeye2.git
 */

namespace loeye\web;

use loeye\error\BusinessException;

/**
 * Response
 *
 * @author   Zhang Yi <loeyae@gmail.com>
 */
class Response extends \loeye\std\Response
{

    public const DEFAULT_RENDER_ID = 'default';
    public const DEFAULT_MOBILE_RENDER_ID = 'mobile';

    private $_renderId = self::DEFAULT_RENDER_ID;
    private $_resource = array();
    private $_htmlHead = array();
    private $_redirectUrl;

    /**
     * addHtmlHead
     *
     * @param string $data data
     *
     * @return void
     */
    public function addHtmlHead($data): void
    {
        $this->_htmlHead[] = $data;
    }

    /**
     * getHtmlHead
     *
     * @return array()
     */
    public function getHtmlHead(): array
    {
        return $this->_htmlHead;
    }

    /**
     * getOutput
     *
     * @return array
     */
    public function getOutput(): array
    {
        return $this->output;
    }

    /**
     * flush
     *
     * @return void
     */
    public function flush(): void
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
    public function setRenderId($renderId): void
    {
        $this->_renderId = $renderId;
    }

    /**
     * getRenderId
     *
     * @return string|null
     */
    public function getRenderId(): ?string
    {
        return $this->_renderId;
    }

    /**
     * addResource
     *
     * @param Resource $resource instance of resource
     *
     * @return void
     */
    public function addResource(Resource $resource): void
    {
        $type                   = $resource->getType();
        $this->_resource[$type] = $resource;
    }

    /**
     * getResource
     *
     * @param string $type type
     *
     * @return Resource|Resource[]|null
     */
    public function getResource($type = null)
    {
        if (isset($type)) {
            return $this->_resource[$type] ?? null;
        }
        return $this->_resource;
    }

    /**
     * getResourceTypes
     *
     * @return array
     */
    public function getResourceTypes(): array
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
    public function setRedirectUrl($url): void
    {
        $this->_redirectUrl = $url;
    }

    /**
     * getRedirectUrl
     *
     * @return string|null
     */
    public function getRedirectUrl(): ?string
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
     * @throws BusinessException
     */
    public function redirect($redirectUrl = null): void
    {
        if (empty($redirectUrl)) {
            $redirectUrl = $this->_redirectUrl;
        }
        if (!empty($redirectUrl)) {
            $this->headers->set('Location', $redirectUrl);
            $this->sendHeaders();
            exit;
        }
        throw new BusinessException(
            BusinessException::INVALID_PARAMETER_MSG,
            BusinessException::INVALID_PARAMETER_CODE
        );
    }

}
