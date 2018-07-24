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
 * @version  GIT: $Id: Zhang Yi $
 * @link     https://github.com/loeyae/loeye.git
 */

namespace loeye\render;

/**
 * Description of HtmlRender
 *
 * @author   Zhang Yi <loeyae@gmail.com>
 */
class HtmlRender implements \loeye\std\Render
{

    /**
     * header
     *
     * @param \LOEYE\Response $response response
     *
     * @return void
     */
    public function header(Response $response)
    {
        $response->addHeader('Content-Type', 'text/html; charset=UTF-8');
        $response->setHeaders();
    }

    /**
     * output
     *
     * @param \loeye\std\Response $response response
     *
     * @return void
     */
    public function output(\loeye\std\Response $response)
    {
        echo '<html>';
        echo '<head>';
        echo $this->_renderHead($response);
        echo $this->_renderResource($response, Resource::RESOURCE_TYPE_CSS);
        echo '</head>';
        echo '<body>';
        echo $this->_renderBody($response);
        echo $this->_renderResource($response, Resource::RESOURCE_TYPE_JS);
        echo '</body>';
        echo '</html>';
    }

    /**
     * _renderHead
     *
     * @param \loeye\std\Response $response response
     *
     * @return string
     */
    private function _renderHead(\loeye\std\Response $response)
    {
        $head = $response->getHtmlHead();
        return implode(PHP_EOL, $head);
    }

    /**
     * _renderBody
     *
     * @param \loeye\std\Response $response response
     *
     * @return string
     */
    private function _renderBody(\loeye\std\Response $response)
    {
        $output = $response->getOutput();
        foreach ($output as $item) {
            $this->_fprint($item);
        }
    }

    /**
     * renderResource
     *
     * @param \loeye\std\Response $response response
     * @param string               $type     type
     *
     * @return string
     */
    private function _renderResource(\loeye\std\Response $response, $type)
    {
        $resource = $response->getResource($type);
        if ($resource !== null) {
            return $resource->toHtml();
        } else {
            return null;
        }
    }

    /**
     * _fprint
     *
     * @param string $item item
     *
     * @reutn void;
     */
    private function _fprint($item)
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
