<?php

/**
 * PrototypedRegexNode.php
 *
 * PHP version 7
 * 
 * Licensed under the Apache License, Version 2.0 (the "License"),
 * see LICENSE for more details: http://www.apache.org/licenses/LICENSE-2.0.
 * 
 * @category PHP
 * @package  LOEYE
 * @author   Zhang Yi <loeyae@gmail.com>
 * @version  2020年1月15日 下午10:50:48
 * @link     https://github.com/loeyae/loeye.git
 */

namespace loeye\config;

/**
 * PrototypedRegexNode
 *
 * @author   Zhang Yi <loeyae@gmail.com>
 */
class PrototypedRegexNode extends PrototypedArrayNode {


    /**
     * Returns a prototype for the child node that is associated to $key in the value array.
     * For general child nodes, this will be $this->prototype.
     * But if $this->removeKeyAttribute is true and there are only two keys in the child node:
     * one is same as this->keyAttribute and the other is 'value', then the prototype will be different.
     *
     * For example, assume $this->keyAttribute is 'name' and the value array is as follows:
     *
     *     [
     *         [
     *             'name' => 'name001',
     *             'value' => 'value001'
     *         ]
     *     ]
     *
     * Now, the key is 0 and the child node is:
     *
     *     [
     *        'name' => 'name001',
     *        'value' => 'value001'
     *     ]
     *
     * When normalizing the value array, the 'name' element will removed from the child node
     * and its value becomes the new key of the child node:
     *
     *     [
     *         'name001' => ['value' => 'value001']
     *     ]
     *
     * Now only 'value' element is left in the child node which can be further simplified into a string:
     *
     *     ['name001' => 'value001']
     *
     * Now, the key becomes 'name001' and the child node becomes 'value001' and
     * the prototype of child node 'name001' should be a ScalarNode instead of an ArrayNode instance.
     *
     * @param string $key
     * @return mixed The prototype instance
     */
    private function getPrototypeForChild(string $key)
    {
        $prototype = $this->valuePrototypes[$key] ?? $this->prototype;
        $prototype->setName($key);

        return $prototype;
    }
}
