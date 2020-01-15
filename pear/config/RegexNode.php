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

/**
 * RegexNode
 *
 * @author   Zhang Yi <loeyae@gmail.com>
 */
class RegexNode extends ArrayNode {


    public function match($value)
    {
        return preg_match($this->getPattern(), $value);
    }


    protected function getPattern()
    {
        if ($this->name == '*') {
            return '#.+#';
        }
        if (\loeye\base\Utils::startwith($this->name, '/')) {
            return $this->name;
        }
        if (\loeye\base\Utils::startwith($this->name, '#')) {
            return $this->name;
        }
        return '#'. $this->name .'#';
    }

}
