<?php

/**
 * HtmlRender.php
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

namespace loeye\render;

use loeye\std\Render;
use loeye\std\Response;
use loeye\web\Resource;

/**
 * Description of HtmlRender
 *
 * @author   Zhang Yi <loeyae@gmail.com>
 */
class HtmlRender implements Render
{

    /**
     * header
     *
     * @param Response $response response
     *
     * @return void
     */
    public function header(Response $response): void
    {
        $response->addHeader('Content-Type', 'text/html; charset=UTF-8');
        $response->setHeaders();
    }

    /**
     * output
     *
     * @param Response $response response
     *
     * @return void
     */
    public function output(Response $response): void
    {
        echo '<!DOCTYPE html>';
        echo '<html lang="zh">';
        echo '<head>';
        echo $this->_renderHead($response);
        echo $this->_renderResource($response, Resource::RESOURCE_TYPE_CSS);
        echo '</head>';
        echo '<body>';
        $this->_renderBody($response);
        echo $this->_renderResource($response, Resource::RESOURCE_TYPE_JS);
        echo '</body>';
        echo '</html>';
    }

    /**
     * _renderHead
     *
     * @param Response $response response
     *
     * @return string
     */
    private function _renderHead(Response $response): string
    {
        $head = $response->getHtmlHead();
        return implode(PHP_EOL, $head);
    }

    /**
     * _renderBody
     *
     * @param Response $response response
     *
     * @return void
     */
    private function _renderBody(Response $response): void
    {
        $output = $response->getOutput();
        foreach ($output as $item) {
            $this->_fprint($item);
        }
    }

    /**
     * renderResource
     *
     * @param Response $response response
     * @param string               $type     type
     *
     * @return string|null
     */
    private function _renderResource(Response $response, $type): ?string
    {
        $resource = $response->getResource($type);
        if ($resource instanceof Resource) {
            return $resource->toHtml();
        }

        return null;
    }

    /**
     * _fprint
     *
     * @param mixed $item item
     *
     * @reutn void;
     */
    private function _fprint($item): void
    {
        if (is_array($item)) {
            foreach ($item as $value) {
                $this->_fprint($value);
            }
        } else {
            echo $item . PHP_EOL;
        }
    }

}
