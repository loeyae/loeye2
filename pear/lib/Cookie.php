<?php

/**
 * Cookie.php
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

/**
 * Description of Cookie
 *
 * @author   Zhang Yi <loeyae@gmail.com>
 */
class Cookie
{

    const UNIQUE_ID_NAME = 'LOUID';
    const USRE_MESSAGE_INFO = 'LOUSI';
    const CRYPT_COOKIE_FIELDS = 'loc';

    /**
     * setCookie
     *
     * @param string $name     name
     * @param mixed  $value    value
     * @param int    $expire   expire time
     * @param string $path     path
     * @param string $domain   domain
     * @param bool   $secure   secure
     * @param bool   $httponly http only
     *
     * @return boolean
     */
    public static function setCookie
            (
            $name,
            $value = null,
            $expire = 0,
            $path = '/',
            $domain = null,
            $secure = false,
            $httponly = false
    )
    {
        return setcookie($name, $value, $expire, $path, $domain, $secure, $httponly);
    }

    /**
     * getCookie
     *
     * @param string $name name
     *
     * @return string
     */
    public static function getCookie($name)
    {
        if (filter_has_var(INPUT_COOKIE, $name)) {
            return filter_input(INPUT_COOKIE, $name);
        }
        return null;
    }

    /**
     * destructCookie
     *
     * @param string $name name
     *
     * @return boolean
     */
    public static function destructCookie($name)
    {
        return setcookie($name, null, -1, '/');
    }

    /**
     * setLoeyeCookie
     *
     * @param string $name  name
     * @param string $value value
     * @param bool   $crypt is crypt
     *
     * @return boolean
     */
    public static function setLoeyeCookie($name, $value, $crypt = false)
    {
        static $userMessageInfo;
        static $cryptFields;
        if (empty($userMessageInfo)) {
            $userMessageInfo = self::getLoeyeCookie(null, $decode=false);
            if (!empty($userMessageInfo[self::CRYPT_COOKIE_FIELDS])) {
                $cryptFields = $userMessageInfo[self::CRYPT_COOKIE_FIELDS];
            } else {
                $cryptFields = array();
            }
        }
        if ($crypt) {
            $userMessageInfo[$name] = self::crypt($value);
            if (!in_array($name, $cryptFields)) {
                $cryptFields[] = $name;
            }
        } else {
            $userMessageInfo[$name] = $value;
        }
        $userMessageInfo[self::CRYPT_COOKIE_FIELDS] = self::crypt(json_encode($cryptFields));
        return self::setCookie(self::USRE_MESSAGE_INFO, json_encode($userMessageInfo));
    }

    /**
     * getLoeyeCookie
     *
     * @param string $name name
     *
     * @return mixed
     */
    public static function getLoeyeCookie($name = null, $decode = true)
    {
        if (filter_has_var(INPUT_COOKIE, self::USRE_MESSAGE_INFO)) {
            $userMessageInfo = json_decode(filter_input(INPUT_COOKIE, self::USRE_MESSAGE_INFO), true);
            $cryptFields     = json_decode(self::crypt($userMessageInfo[self::CRYPT_COOKIE_FIELDS], true), true);
            if (!empty($cryptFields) && $decode) {
                foreach ($userMessageInfo as $key => $value) {
                    if (in_array($key, $cryptFields)) {
                        $userMessageInfo[$key] = self::crypt($value, true);
                    }
                }
            }
            $userMessageInfo[self::CRYPT_COOKIE_FIELDS] = $cryptFields;
            if (!empty($name)) {
                return isset($userMessageInfo[$name]) ? $userMessageInfo[$name] : null;
            }
            return $userMessageInfo;
        }
        return null;
    }

    /**
     * crypt
     *
     * @param string $data   data
     * @param bool   $decode is decode
     * @param string $key    key
     *
     * @return string
     */
    public static function crypt($data, $decode = false, $key = null)
    {
        $key = empty($key) ? self::uniqueId() : $key;
        return Secure::crypt($key, $data, $decode);
    }

    /**
     * getUniqueId
     *
     * @return string
     */
    public static function uniqueId()
    {
        $sessionId = session_id();
        if ($sessionId) {
            return $sessionId;
        }
        if (filter_has_var(INPUT_COOKIE, self::UNIQUE_ID_NAME)) {
            return filter_input(INPUT_COOKIE, self::UNIQUE_ID_NAME);
        }
        $uniqueId = Secure::uniqueId();
        self::setCookie(self::UNIQUE_ID_NAME, $uniqueId);
        return $uniqueId;
    }

    /**
     * createCrumb
     *
     * @param string $name name
     *
     * @return string
     */
    public static function createCrumb($name)
    {
        $uid    = self::uniqueId();
        $string = $name . md5(($name . $uid));
        return hash('crc32', $string);
    }

    /**
     * validateCrumb
     *
     * @param string $name  name
     * @param string $crumb crumb
     *
     * @return boolean
     */
    public static function validateCrumb($name, $crumb)
    {
        $ocrumb = self::createCrumb($name);
        return ($ocrumb == $crumb);
    }

}
