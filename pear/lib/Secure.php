<?php

/**
 * Secure.php
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

namespace loeye\lib;

use http\Exception\RuntimeException;

/**
 * Description of Secure
 *
 * @author   Zhang Yi <loeyae@gmail.com>
 */
class Secure
{

    /**
     * getKey
     *
     * @param mixed $value value
     *
     * @return string
     */
    public static function getKey($value)
    {
        return md5(serialize($value));
    }

    /**
     * uniqueId
     *
     * @param string $secret secret key
     *
     * @return string
     */
    public static function uniqueId($secret = null)
    {
        if (filter_has_var(INPUT_SERVER, 'REQUEST_TIME_FLOAT')) {
            $REQUEST_TIME_FLOAT = filter_input(INPUT_SERVER, 'REQUEST_TIME_FLOAT', FILTER_SANITIZE_NUMBER_FLOAT);
        } else if (isset($_SERVER['REQUEST_TIME_FLOAT'])) {
            $REQUEST_TIME_FLOAT = filter_var($_SERVER['REQUEST_TIME_FLOAT'], FILTER_SANITIZE_NUMBER_FLOAT);
        } else {
            $REQUEST_TIME_FLOAT = time();
        }

        if (filter_has_var(INPUT_SERVER, 'HTTP_USER_AGENT')) {
            $HTTP_USER_AGENT = filter_input(INPUT_SERVER, 'HTTP_USER_AGENT', FILTER_SANITIZE_STRING);
        } else if (isset($_SERVER['HTTP_USER_AGENT'])) {
            $HTTP_USER_AGENT = filter_var($_SERVER['HTTP_USER_AGENT'], FILTER_SANITIZE_STRING);
        } else {
            $HTTP_USER_AGENT = mt_rand(10000, 99999);
        }

        if (filter_has_var(INPUT_SERVER, 'REMOTE_ADDR')) {
            $REMOTE_ADDR = filter_input(INPUT_SERVER, 'REMOTE_ADDR', FILTER_VALIDATE_IP);
        } else if (isset($_SERVER['REMOTE_ADDR'])) {
            $REMOTE_ADDR = filter_var($_SERVER['REMOTE_ADDR'], FILTER_VALIDATE_IP);
        } else {
            $REMOTE_ADDR = mt_rand(1000, 9999);
        }

        $string   = $HTTP_USER_AGENT . $REQUEST_TIME_FLOAT . $REMOTE_ADDR . $secret;
        $string   .= md5(mt_rand(1, time()));
        $uniqueId = hash('haval160,5', sha1($string));
        return $uniqueId;
    }

    /**
     * crypt
     *
     * @param string $key    key
     * @param string $data   data
     * @param bool   $decode is decode
     *
     * @return string
     */
    public static function crypt($key, $data, $decode = false): string
    {
        $len          = ceil(strlen($key) / 3) * 3;
        $secretKey    = base64_encode(str_pad($key, $len, $key[0], STR_PAD_RIGHT));
        $secretKeyLen = strlen($secretKey);

        $string = ($decode === true) ? base64_decode($data) : rawurlencode($data) . $secretKey;
        if ($string === false) {
            return $data;
        }
        $keySize = 64;
        if ($secretKeyLen > $keySize) {
            $mkey = substr($secretKey, 0, $keySize);
        } else {
            $mkey = str_pad($secretKey, $keySize, $key[0], STR_PAD_RIGHT);
        }

        $method = 'AES-256-CBC';
        $ivLength = openssl_cipher_iv_length($method);

        if ($decode === false) {
            $cryptoStrong = null;
            $iv = openssl_random_pseudo_bytes($ivLength, $cryptoStrong);
            if ($cryptoStrong === false || $iv === false) {
                throw new \RuntimeException('crypto failed');
            }
            $strLen = strlen($string);
            $padLen = $strLen % 8;
            if ($padLen !== 0) {
                $string = str_pad($string, $strLen + $padLen, "\0", STR_PAD_RIGHT);
            }
            $mcryptString = base64_encode(openssl_encrypt($string, $method, $mkey, OPENSSL_RAW_DATA, $iv) . $iv);
            return trim($mcryptString, '=');
        }

        $decodeIv = substr($string, 0 - $ivLength);
        $result = rtrim(openssl_decrypt(substr($string, 0, 0-$ivLength), $method, $mkey, OPENSSL_RAW_DATA, $decodeIv), "\0");
        if (substr($result, -($secretKeyLen)) === $secretKey) {
            $result = substr($result, 0, -($secretKeyLen));
            return rawurldecode($result);
        }
        return '';
    }

    /**
     * getKeyDb
     *
     * @param string $property property name
     * @param string $key      key
     * @param string $group    group
     * @param string $read     false
     *
     * @return string
     */
    public static function getKeyDb($property, $key, $group = null, $read = false)
    {
        static $keyDbSetting = [];
        $cache               = SimpleCache::getInstance($property, 'keydb');
        $keyDbSetting        = $cache->get('keydb');
        if (empty($keyDbSetting)) {
            $baseDir      = PROJECT_KEYDB_DIR . '/' . $property;
            $dirIterator  = new \RecursiveDirectoryIterator($baseDir, \FilesystemIterator::UNIX_PATHS);
            $fileSystem   = new \RecursiveIteratorIterator($dirIterator);
            $keyDbSetting = array();
            $XMReader     = new \XMLReader();
            foreach ($fileSystem as $file) {
                if ($file->getExtension() != 'keydb') {
                    continue;
                }
                $settings = self::readKeydb($file, $XMReader);
                if (!empty($settings)) {
                    $init = $keyDbSetting;
                    $keyDbSetting = array_merge_recursive($init, $settings);
                }
            }
            if ($keyDbSetting) {
                $cache->set('keydb', $keyDbSetting);
            }
        }
        if ($read === true) {
            return;
        }
        foreach ($keyDbSetting as $k => $child) {
            if ($group !== null) {
                if ($k == $group && isset($child[$key])) {
                    $time   = $child[$key]['timestamp'];
                    $expiry = $child[$key]['expiry'];
                    if ($time == $expiry) {
                        return self::crypt($key, $child[$key]['value'], true);
                    } else if (time() < $expiry) {
                        return self::crypt($key, $child[$key]['value'], true);
                    }
                }
            } else {
                if (isset($child[$key])) {
                    $time   = $child[$key]['timestamp'];
                    $expiry = $child[$key]['expiry'];
                    if ($time == $expiry) {
                        return self::crypt($key, $child[$key]['value'], true);
                    } else if (time() < $expiry) {
                        return self::crypt($key, $child[$key]['value'], true);
                    }
                }
            }
        }
        return null;
    }

    /**
     * setKeyDb
     *
     * @param string $property property
     * @param string $keydb    key db
     * @param string $key      key
     * @param string $value    value
     * @param string $group    group
     * @param int    $expiry   expiry
     *
     * @return void
     */
    public static function setKeyDb($property, $keydb, $key, $value, $group = null, $expiry = 0)
    {
        $baseDir = PROJECT_KEYDB_DIR . '/' . $property;
        if (!file_exists($baseDir) && !mkdir($baseDir, 0777, true) && !is_dir($baseDir)) {
            throw new \RuntimeException(sprintf('Directory "%s" was not created', $baseDir));
        }
        $filename = $baseDir . '/' . $keydb . '.keydb';
        $settings = array();
        if (!file_exists($filename)) {
            file_put_contents($filename, '<keydb></keydb>');
        } else {
            $settings = self::readKeydb($filename);
        }
        $XMLWriter = new \XMLWriter();
        $XMLWriter->openUri($filename);
        $XMLWriter->setIndent(true);
        $XMLWriter->startElement("keydb");
        empty($group) && $group     = $keydb;
        $writed    = 0;
        foreach ($settings as $groupName => $groupSettings) {
            $XMLWriter->startElement("keygroup");
            $XMLWriter->writeAttribute("name", $groupName);
            if ($group == $groupName) {
                $writed = 1;
            }
            foreach ($groupSettings as $keyName => $keySetting) {
                if ($writed == 1 && $keyName == $key) {
                    $writed = 2;
                    self::writeKeydb($XMLWriter, $key, $value, null, $expiry);
                    continue;
                } else if ($writed == 2 && $keyName == $key) {
                    continue;
                }
                $kvalue    = isset($keySetting['value']) ? $keySetting['value'] : '';
                $timestamp = isset($keySetting['timestamp']) ? $keySetting['timestamp'] : 0;
                $kexpiry   = isset($keySetting['expiry']) ? $keySetting['expiry'] : '';
                self::writeKeydb($XMLWriter, $keyName, $kvalue, null, $kexpiry, $timestamp, true);
            }
            if ($writed == 1) {
                self::writeKeydb($XMLWriter, $key, $value, null, $expiry);
            }
            $XMLWriter->fullEndElement();
        }
        if ($writed == 0) {
            self::writeKeydb($XMLWriter, $key, $value, $group, $expiry);
        }
        $XMLWriter->fullEndElement();
        $XMLWriter->flush();
    }

    /**
     * readKeydb
     *
     * @param string     $file     file
     * @param \XMLReader $XMReader XML reader
     *
     * @return array
     */
    static private function readKeydb($file, \XMLReader $XMReader = null)
    {
        $keyDbSetting = array();
        (!($XMReader instanceof \XMLReader)) && $XMReader     = new \XMLReader();
        $openTag      = $XMReader->open($file, 'utf-8');
        if ($openTag === false) {
            return $keyDbSetting;
        }
        while ($XMReader->read()) {
            if ($XMReader->nodeType !== \XMLReader::ELEMENT) {
                continue;
            }
            switch ($XMReader->name) {
                case "keygroup":
                    $keygroup                                       = $XMReader->getAttribute('name');
                    $keyDbSetting[$keygroup]                        = array();
                    break;
                case "keyname":
                    $keyname                                        = $XMReader->getAttribute('name');
                    $keyDbSetting[$keygroup][$keyname]              = array();
                    break;
                case "key":
                    $keyDbSetting[$keygroup][$keyname]['value']     = $XMReader->getAttribute('value');
                    $keyDbSetting[$keygroup][$keyname]['timestamp'] = $XMReader->getAttribute('timestamp');
                    $keyDbSetting[$keygroup][$keyname]['expiry']    = $XMReader->getAttribute('expiry');
                    break;
            }
            //$XMReader->next();
        }
        $XMReader->close();
        return $keyDbSetting;
    }

    /**
     * writeKeydb
     *
     * @param mixed  $xml    fime|XMLWriter
     * @param string $key    key
     * @param mixed  $value  value
     * @param mixed  $group  group name
     * @param int    $expiry expiry
     * @param int    $time   time
     *
     * @return void
     */
    static private function writeKeydb(
            $xml, $key, $value, $group = null, $expiry = 0, $time = null, $isSecure = false
    )
    {
        if (!($xml instanceof \XMLWriter)) {
            $XMLWriter = new \XMLWriter();
            $XMLWriter->openUri($xml);
            $XMLWriter->setIndent(true);
            $XMLWriter->startElement("keydb");
            if (empty($group)) {
                $group = $key;
            }
        } else {
            $XMLWriter = $xml;
        }
        if (!empty($group)) {
            $XMLWriter->startElement("keygroup");
            $XMLWriter->writeAttribute("name", $group);
        }
        $XMLWriter->startElement("keyname");
        $XMLWriter->writeAttribute("name", $key);
        $XMLWriter->startElement("key");
        $XMLWriter->writeAttribute("value", ($isSecure ? $value : self::crypt($key, $value)));
        ($time == null) && ($time   = time());
        ($expiry = 0) ? ($expiry = $time) : ($expiry += $time);
        $XMLWriter->writeAttribute("timestamp", $time);
        $XMLWriter->writeAttribute("expiry", $expiry);
        $XMLWriter->fullEndElement();
        $XMLWriter->fullEndElement();
        if (!empty($group)) {
            $XMLWriter->fullEndElement();
        }
        if (!($xml instanceof \XMLWriter)) {
            $XMLWriter->fullEndElement();
            $XMLWriter->flush();
        }
    }

    /**
     * encodeUid
     *
     * @param string $id         user id
     * @param int    $createTime time stamp
     *
     * @return string
     */
    public static function encodeUid($id, $createTime)
    {
        $num1 = date('Y', $createTime) % 36;
        $num2 = ceil(date('Z', $createTime) / 15) % 36;
        $chr1 = ($num1 < 10) ? $num1 : chr($num1 + 87);
        $chr2 = ($num2 < 10) ? $num2 : chr($num2 + 87);
        return $id . '#' . $chr1 . $chr2;
    }

    /**
     * decodeUid
     *
     * @param string $uid user uid
     *
     * @return string
     */
    public static function decodeUid($uid)
    {
        $data = explode('#', $uid);
        array_pop($data);
        return implode('#', $data);
    }

}
