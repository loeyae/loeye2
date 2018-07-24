<?php

/**
 * QRCodePlugin.php
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

namespace loeye\plugin;

$file = LOEYE_BASE_LIB_DIR . DIRECTORY_SEPARATOR . 'phpqrcode' . DIRECTORY_SEPARATOR . 'qrlib.php';

AutoLoadRegister::loadFile($file);

/**
 * QRCodePlugin
 *
 * @author   Zhang Yi <loeyae@gmail.com>
 */
class QRCodePlugin extends \loeye\std\Plugin
{

    /**
     * process
     *
     * @param \LOEYE\Context $context context
     * @param array               $inputs  inputs
     *
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function process(\LOEYE\Context $context, array $inputs)
    {
        $text   = \loeye\base\Utils::checkNotEmptyContextData($context, $inputs);
        $file   = \loeye\base\Utils::getData($context, 'file');
        $level  = \loeye\base\Utils::getData($inputs, 'level', QR_ECLEVEL_L);
        $size   = \loeye\base\Utils::getData($inputs, 'size', 6);
        $margin = \loeye\base\Utils::getData($inputs, 'margin', 4);
        if ($file) {
            \QRcode::png($text, $file, $level, $size, $margin);
        } else {
            $context->getResponse()->setFormat(RENDER_TYPE_SEGMENT);
            $context->getResponse()->addHeader('Content-Type', 'image/png');
            ob_start();
            \QRcode::png($text, false, $level, $size, $margin);
            $content = ob_get_flush();
            ob_end_clean();
            $context->getResponse()->addOutput($content);
            return false;
        }
    }

}
