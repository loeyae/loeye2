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
 * @version  2018-07-23 22:44:28
 * @link     https://github.com/loeyae/loeye2.git
 */

namespace loeye\plugin;

use loeye\base\Context;
use loeye\base\Exception;
use loeye\base\Utils;
use loeye\error\BusinessException;
use loeye\std\Plugin;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * UploadPlugin
 *
 * @author   Zhang Yi <loeyae@gmail.com>
 */
class UploadPlugin implements Plugin
{
    /**
     * @var mixed|null
     */
    private $baseUploadDir;

    /**
     * process
     *
     * @param Context $context context
     * @param array $inputs inputs
     *
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @throws Exception
     */
    public function process(Context $context, array $inputs): void
    {
        $data = Utils::checkNotEmptyContextData($context, $inputs, 'UploadPlugin_input');
        $path = $this->_getUploadPath($inputs);
        $files = $this->uploadFile($data, $path, $inputs);
        Utils::setContextData($this->_matchUrl($files, $path, $inputs), $context, $inputs, 'UploadPlugin_data');
    }

    /**
     * uploadFile
     *
     * @param $data
     * @param $path
     * @param array $inputs
     * @return array|\Symfony\Component\HttpFoundation\File\File
     * @throws Exception
     */
    private function uploadFile($data, $path, array $inputs)
    {
        if (is_array($data)) {
            $ret = [];
            foreach ($data as $key => $file) {
                $ret[$key] = $this->uploadFile($file, $path, $inputs);
            }
            return $ret;
        } else if ($data instanceof UploadedFile) {
            return $data->move($path, $this->_getFileName($data, $inputs, $path));
        }
    }

    /**
     * _matchUrl
     *
     * @param mixed $data data
     * @param string $path
     * @param mixed $inputs input
     *
     * @return array
     * @throws Exception
     */
    private function _matchUrl($data, $path, array $inputs): array
    {
        $url = array();
        if (is_array($data)) {
            foreach ($data as $key => $file) {
                $url[$key] = $this->_matchUrl($file, $path, $inputs);
            }
        } else {
            if (!defined('PROJECT_UPLOAD_BASE_DIR')) {
                throw new Exception('no constant: PROJECT_UPLOAD_BASE_DIR');
            }
            $upload = Utils::getData($inputs, 'base_url', 'upload');
            $replace = (defined('BASE_SERVER_URL') ? trim(BASE_SERVER_URL, '/') : '') . '/' . $upload;
            $r = str_replace([$this->baseUploadDir, '\\'], [$replace, '/'], $data);
            $url = array(
                'name' => pathinfo($data, PATHINFO_BASENAME),
                'url' => $r,
                'path' => str_replace([PROJECT_UPLOAD_BASE_DIR, '\\'], ['/' . $upload, '/'], $data),
            );
        }
        return $url;
    }

    /**
     * _moveUploadFile
     *
     * @param string $tmpFile temp file
     * @param string $fileName file name
     *
     * @return string
     * @throws Exception
     */
    private function _moveUploadFile($tmpFile, $fileName): string
    {
        if (is_uploaded_file($tmpFile) && move_uploaded_file($tmpFile, $fileName)) {
            return $fileName;
        }
        $errorMessage = 'file upload failed';
        throw new Exception($errorMessage, Exception::DEFAULT_ERROR_CODE);
    }

    /**
     * _getFileName
     *
     * @param UploadedFile $file file info
     * @param array $inputs inputs
     * @param string $path
     *
     * @return string
     * @throws Exception
     */
    private function _getFileName(UploadedFile $file, array $inputs, $path): string
    {
        $keep = Utils::getData($inputs, 'keep', null);
        $ext = $file->getClientOriginalExtension();
        $min = 1000;
        $max = 9999;
        if ($keep === 'true') {
            $fileName = $file->getClientOriginalName();
            while (is_file($path .'/'. $fileName . '.' . $ext)) {
                $count = mt_rand($min, $max);
                $fileName .= '_' . $count;
            }
        } else {
            [$usec, $sec] = explode(" ", microtime());
            $fileName = md5($file->getClientOriginalName())
                . '_' . $sec . str_replace('0.', '_', $usec);
            while (is_file( $path . '/' . $fileName . '.' . $ext)) {
                $count = mt_rand($min, $max);
                $fileName .= '_' . $count;
            }
        }
        return $path .'/'. $fileName . '.' . $ext;
    }

    /**
     * _getExt
     *
     * @param string $name file name
     *
     * @return string
     */
    private function _getExt($name): string
    {
        return mb_substr(mb_strrchr($name, '.'), 1);
    }

    /**
     * _getUploadPath
     *
     * @param array $inputs inputs
     *
     * @return string
     * @throws Exception
     */
    private function _getUploadPath(array $inputs): string
    {
        $uploadPath = Utils::getData($inputs, 'base_dir', null);
        if ($uploadPath === null) {
            if (defined('PROJECT_UPLOAD_BASE_DIR')) {
                $uploadPath = PROJECT_UPLOAD_BASE_DIR;
            } else {
                $errorMessage = 'upload base dir not set';
                throw new Exception(
                    $errorMessage, BusinessException::INVALID_PLUGIN_SET_CODE);
            }
        }
        $this->baseUploadDir = $uploadPath;
        $dir = Utils::getData($inputs, 'path', null);
        if (!empty($dir)) {
            $uploadPath .= '/' . $dir;
        }
        if (!is_dir($uploadPath)) {
            $errorMessage = 'upload dir not found';
            throw new Exception($errorMessage, Exception::DEFAULT_ERROR_CODE);
        }
        $dateSplit = Utils::getData($inputs, 'split', false);
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
        }
        if (!file_exists($uploadPath) && (mkdir($uploadPath, 0777, true) || !is_dir($uploadPath))) {
            $errorMessage = 'mkdir failed';
            throw new Exception(
                $errorMessage, BusinessException::DEFAULT_ERROR_CODE);
        }
        return $uploadPath;
    }

}
