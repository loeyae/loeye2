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

use loeye\base\Context;
use loeye\base\Utils;
use loeye\error\ResourceException;
use loeye\std\Plugin;

/**
 * DataObjectPlugin
 *
 * @author   Zhang Yi <loeyae@gmail.com>
 */
class DataObjectPlugin implements Plugin
{

    /**
     * process
     *
     * @param Context $context context
     * @param array $inputs inputs
     *
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @throws ResourceException
     */
    public function process(Context $context, array $inputs): void
    {
        $dataPath   = Utils::checkNotEmpty($inputs, 'path');
        $dataObject = Utils::checkNotEmpty($inputs, 'out');
        $file       = realpath(PROJECT_DATA_DIR . '/' . $dataPath);
        if (is_file($file)) {
            $data = file_get_contents($file);
            $context->set($dataObject, json_decode($data, true));
        } else {
            throw new ResourceException(
            ResourceException::FILE_NOT_FOUND_MSG, ResourceException::FILE_NOT_FOUND_CODE, ['file' => $file]);
        }
    }

}
