<?php

/**
 * UploadPlugin.php
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

/**
 * UploadPlugin
 *
 * @author   Zhang Yi <loeyae@gmail.com>
 */
class UploadPlugin extends \loeye\std\Plugin
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
        $fields       = \loeye\base\Utils::checkNotEmpty($inputs, 'field');
        $data         = array();
        $errorMessage = 'no file upload';
        foreach ((array) $fields as $field => $out) {
            if (!isset($_FILES[$field])) {
                $data[$field] = new \loeye\base\Exception(
                        $errorMessage, \loeye\base\Exception::DEFAULT_ERROR_CODE);
                \loeye\base\Utils::addErrors($data[$field], $context, $inputs, $out . '_error');
                continue;
            }
            if (is_array($_FILES[$field]['name'])) {
                foreach ($_FILES[$field]['name'] as $key => $name) {
                    if ($_FILES[$field]['error'][$key] !== 0) {
                        $data[$field][$key] = new \loeye\base\Exception(
                                $errorMessage, \loeye\base\Exception::DEFAULT_ERROR_CODE);
                        continue;
                    }
                    try {
                        $file               = array('name' => $name);
                        $fileName           = $this->_getFileName($file, $inputs);
                        $data[$field][$key] = $this->_moveUploadFile($_FILES[$field]['tmp_name'][$key], $fileName);
                    } catch (Exception $ex) {
                        $data[$field][$key] = $ex;
                    }
                }
                $uploadData  = array();
                $uploadError = array();
                \loeye\base\Utils::filterResultArray($data[$field], $uploadData, $uploadError);
                if (!empty($uploadError)) {
                    $context->addErrors($out . '_error', $uploadError);
                }
                $context->set($out, $this->_matchUrl((array) $uploadData, $inputs));
            } else {
                $uploadData  = array();
                $uploadError = null;
                if ($_FILES[$field]['error'] === 0) {
                    try {
                        $fileName     = $this->_getFileName($_FILES[$field], $inputs);
                        $data[$field] = $this->_moveUploadFile($_FILES[$field]['tmp_name'], $fileName);
                    } catch (Exception $ex) {
                        $data[$field] = $ex;
                    }
                } else {
                    $data[$field] = new \loeye\base\Exception(
                            $errorMessage, \loeye\base\Exception::DEFAULT_ERROR_CODE);
                }
                \loeye\base\Utils::filterResult($data[$field], $uploadData, $uploadError);
                if (!empty($uploadError)) {
                    $context->addErrors($out . '_error', $uploadError);
                }
                $context->set($out, $this->_matchUrl($uploadData, $inputs));
            }
        }
    }

    /**
     * _matchUrl
     *
     * @param mixed $data   data
     * @param mixed $inputs input
     *
     * @return array
     */
    private function _matchUrl($data, array $inputs)
    {
        $url = array();
        if (is_array($data)) {
            foreach ($data as $key => $file) {
                $url[$key] = $this->_matchUrl($file, $inputs);
            }
        } else {
            $upload  = \loeye\base\Utils::getData($inputs, 'base_url', 'upload');
            $path    = $this->_getUploadPath(array());
            $replace = (defined('BASE_SERVER_URL') ? BASE_SERVER_URL : '') . '/' . $upload;
            $r       = str_replace($path, $replace, $data);
            $url     = array(
                'name' => pathinfo($data, PATHINFO_BASENAME),
                'file' => $data,
                'url'  => $r,
                'path' => str_replace(PROJECT_UPLOAD_BASE_DIR, '/' . $upload, $data),
            );
        }
        return $url;
    }

    /**
     * _moveUploadFile
     *
     * @param string $tmpFile  temp file
     * @param string $fileName file name
     *
     * @return boolean
     */
    private function _moveUploadFile($tmpFile, $fileName)
    {
        if (is_uploaded_file($tmpFile) && move_uploaded_file($tmpFile, $fileName)) {
            return $fileName;
        }
        $errorMessage = 'file upload failed';
        return new \loeye\base\Exception($errorMessage, \loeye\base\Exception::DEFAULT_ERROR_CODE);
    }

    /**
     * _getFileName
     *
     * @param array $file   file info
     * @param array $inputs inputs
     *
     * @return string
     */
    private function _getFileName(array $file, array $inputs)
    {
        $keep = \loeye\base\Utils::getData($inputs, 'keey', null);
        \loeye\base\Utils::checkNotEmpty($file, 'name');
        $path = $this->_getUploadPath($inputs);
        $ext  = $this->_getExt($file['name']);
        $min  = 1000;
        $max  = 9999;
        if ($keep === 'true') {
            $fileName = $path . '/' . $file['name'] . '.' . $ext;
            $count    = mt_rand($min, $max);
            while (is_file($fileName)) {
                $fileName = $path . '/' . $file['name'] . '_' . $count . '.' . $ext;
                $count    = mt_rand($min, $max);
            }
        } else {
            list($usec, $sec) = explode(" ", microtime());
            $count    = mt_rand($min, $max);
            $fileName = $path . '/' . md5($file['name'])
                    . '_' . $sec . str_replace('0.', '_', $usec) . '_' . $count . '.' . $ext;
            while (is_file($fileName)) {
                $count    = mt_rand($min, $max);
                $fileName = $path . '/' . md5($file['name'])
                        . '_' . $sec . str_replace('0.', '_', $usec) . '_' . $count . '.' . $ext;
            }
        }
        return $fileName;
    }

    /**
     * _getExt
     *
     * @param string $name file name
     *
     * @return string
     */
    private function _getExt($name)
    {
        $ext = mb_substr(mb_strrchr($name, '.'), 1);
        return $ext;
    }

    /**
     * _getUploadPath
     *
     * @param array $inputs inputs
     *
     * @return string
     * @throws \loeye\base\Exception
     */
    private function _getUploadPath(array $inputs)
    {
        $uploadPath = \loeye\base\Utils::getData($inputs, 'base_dir', null);
        if ($uploadPath === null) {
            if (defined('PROJECT_UPLOAD_BASE_DIR')) {
                $uploadPath = PROJECT_UPLOAD_BASE_DIR;
            } else {
                $errorMessage = 'upload base dir not set';
                throw new \loeye\base\Exception(
                        $errorMessage, \loeye\base\Exception::INVALID_PLUGIN_SET_CODE);
            }
        }
        $dir = \loeye\base\Utils::getData($inputs, 'path', null);
        if (!empty($dir)) {
            $uploadPath .= '/' . $dir;
        }
        if (!is_dir($uploadPath)) {
            $errorMessage = 'upload dir not found';
            throw new \loeye\base\Exception($errorMessage, \loeye\base\Exception::DEFAULT_ERROR_CODE);
        }
        $dateSplit = \loeye\base\Utils::getData($inputs, 'split', false);
        switch ($dateSplit) {
            case 'year':
                $path = date('Y');
                break;
            case 'month':
                $path = date('Y-m');
                break;
            case 'day':
                $path = date('Y-m-d');
                break;
            default :
                $path = null;
                break;
        }
        if (!empty($path)) {
            $uploadPath .= '/' . $path;
            if (!is_dir($uploadPath)) {
                if (mkdir($uploadPath, 0777) == false) {
                    $errorMessage = 'mkdir failed';
                    throw new \loeye\base\Exception(
                            $errorMessage, \loeye\base\Exception::DEFAULT_ERROR_CODE);
                }
            }
        }
        return $uploadPath;
    }

}
