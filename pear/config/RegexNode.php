<?php

/**
 * RegexNode.php
 *
 * PHP version 7
 *
 * Licensed under the Apache License, Version 2.0 (the "License"),
 * see LICENSE for more details: http://www.apache.org/licenses/LICENSE-2.0.
 *
 * @category PHP
 * @package  LOEYE
 * @author   Zhang Yi <loeyae@gmail.com>
 * @version  2020年1月15日 下午10:06:01
 * @link     https://github.com/loeyae/loeye.git
 */

namespace loeye\config;

use loeye\base\Utils;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\Config\Definition\Exception\InvalidTypeException;

/**
 * RegexNode
 *
 * @author   Zhang Yi <loeyae@gmail.com>
 */
class RegexNode extends ArrayNode {


    /**
     * match
     *
     * @param string $value
     * @return bool
     */
    public function match($value): bool
    {
        return preg_match($this->getPattern(), $value);
    }


    /**
     * getPattern
     *
     * @return string
     */
    protected function getPattern(): string
    {
        if ($this->name === '*') {
            return '#.+#';
        }
        if (Utils::startWith($this->name, '/')) {
            return $this->name;
        }
        if (Utils::startWith($this->name, '#')) {
            return $this->name;
        }
        return '#'. $this->name .'#';
    }


    /**
     * Normalizes the value.
     *
     * @param mixed $value The value to normalize
     *
     * @return mixed The normalized value
     *
     * @throws InvalidConfigurationException
     */
    protected function normalizeValue($value)
    {
        if (false === $value) {
            return $value;
        }
        if (is_array($value)) {
            return parent::normalizeValue($value);
        }
        return $value;
    }


    /**
     * Validates the type of the value.
     *
     * @param mixed $value
     *
     * @throws InvalidTypeException
     */
    protected function validateType($value)
    {
    }

}
