<?php
/**
 * Router.php
 *
 * PHP version 7
 *
 * Licensed under the Apache License, Version 2.0 (the "License"),
 * see LICENSE for more details: http://www.apache.org/licenses/LICENSE-2.0.
 *
 * @category PHP
 * @package  LOEYE
 * @author   Zhang Yi <loeyae@gmail.com>
 * @version  2020/4/12 20:05
 * @link     https://github.com/loeyae/loeye2.git
 */


namespace loeye\std;


abstract class Router
{


    /**
     * @var string
     */
    private $matchedRule;

    /**
     * @var array
     */
    private $matchedData;

    /**
     * @var array
     */
    private $pathVariable;

    /**
     * @var array
     */
    private $settings;

    /**
     * setMatchedRule
     *
     * @param string $matchedRule
     * @return Router
     */
    public function setMatchedRule($matchedRule): Router
    {
        $this->matchedRule = $matchedRule;
        return $this;
    }

    /**
     * getMatchedRule
     *
     * @return string|null
     */
    public function getMatchedRule(): ?string
    {
        return $this->matchedRule;
    }

    /**
     * setMatchedData
     *
     * @param $matchedData
     * @return Router
     */
    public function setMatchedData($matchedData): Router
    {
        $this->matchedData = $matchedData;
        return $this;
    }

    /**
     * getMatchedData
     *
     * @return array|null
     */
    public function getMatchedData(): ?array
    {
        return $this->matchedData;
    }

    /**
     * setParameter
     *
     * @param array $pathVariable
     * @return Router
     */
    public function setPathVariable(array $pathVariable): Router
    {
        $this->pathVariable = $pathVariable;
        return $this;
    }

    /**
     * getParameter
     *
     * @return array|null
     */
    public function getPathVariable(): ?array
    {
        return $this->pathVariable;
    }

    /**
     * addParameter
     *
     * @param string $key
     * @param mixed $value
     * @return Router
     */
    public function addPathVariable($key, $value): Router
    {
        $this->pathVariable[$key] = $value;
        return $this;
    }

    /**
     * setSettings
     *
     * @param array $settings
     * @return Router
     */
    public function setSettings(array $settings): Router
    {
        $this->settings = $settings;
        return $this;
    }

    /**
     * getSettings
     *
     * @return array|null
     */
    public function getSettings(): ?array
    {
        return $this->settings;
    }

    /**
     * addSetting
     *
     * @param string $key
     * @param mixed $value
     * @return Router
     */
    public function addSetting($key, $value): Router
    {
        $this->settings[$key] = $value;
        return $this;
    }

    /**
     * match
     *
     * @param string $url url
     *
     * @return mixed
     */
    abstract public function match($url);

}