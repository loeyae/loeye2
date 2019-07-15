<?php

/**
 * DataObjectPlugin.php
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

namespace loeye\plugin;

/**
 * DataObjectPlugin
 *
 * @author   Zhang Yi <loeyae@gmail.com>
 */
class DataObjectPlugin extends \loeye\std\Plugin
{

    /**
     * process
     *
     * @param \loeye\base\Context $context context
     * @param array               $inputs  inputs
     *
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function process(\loeye\base\Context $context, array $inputs)
    {
        $dataPath   = \loeye\base\Utils::checkNotEmpty($inputs, 'path');
        $dataObject = \loeye\base\Utils::checkNotEmpty($inputs, 'out');
        $file       = realpath(PROJECT_DATA_DIR . '/' . $dataPath);
        if (is_file($file)) {
            $data = file_get_contents($file);
            $context->set($dataObject, json_decode($data, true));
        } else {
            $errorMessage = 'File: ' . $file . ' not found';
            throw new \loeye\error\ResourceException(
                    $errorMessage, \loeye\error\ResourceException::FILE_NOT_FOUND_CODE);
        }
    }

}
