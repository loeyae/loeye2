<?php

/**
 * PrototypedArrayNode.php
 *
 * PHP version 7
 *
 * Licensed under the Apache License, Version 2.0 (the "License"),
 * see LICENSE for more details: http://www.apache.org/licenses/LICENSE-2.0.
 *
 * @category PHP
 * @package  LOEYE
 * @author   Zhang Yi <loeyae@gmail.com>
 * @version  2020年1月15日 下午10:39:56
 * @link     https://github.com/loeyae/loeye.git
 */

namespace loeye\config;
use RuntimeException;
use Symfony\Component\Config\Definition\Exception\DuplicateKeyException;
use \Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

/**
 * PrototypedArrayNode
 *
 * @author   Zhang Yi <loeyae@gmail.com>
 */
class PrototypedArrayNode extends \Symfony\Component\Config\Definition\PrototypedArrayNode {


    /**
     * Normalizes the value.
     *
     * @param mixed $value The value to normalize
     *
     * @return mixed The normalized value
     *
     * @throws InvalidConfigurationException
     * @throws DuplicateKeyException
     */
    protected function normalizeValue($value)
    {
        if (false === $value) {
            return $value;
        }

        $value = $this->remapXml($value);
        $isAssoc = array_keys($value) !== range(0, count($value) - 1);
        $normalized = [];
        foreach ($value as $k => $v) {
            if (null !== $this->keyAttribute && is_array($v)) {
                if (!isset($v[$this->keyAttribute]) && is_int($k) && !$isAssoc) {
                    $ex = new InvalidConfigurationException(sprintf('The attribute "%s" must be set for path "%s".',
                        $this->keyAttribute, $this->getPath()));
                    $ex->setPath($this->getPath());

                    throw $ex;
                }

                if (isset($v[$this->keyAttribute])) {
                    $k = $v[$this->keyAttribute];

                    // remove the key attribute when required
                    if ($this->removeKeyAttribute) {
                        unset($v[$this->keyAttribute]);
                    }

                    // if only "value" is left
                    if (array_keys($v) === ['value']) {
                        $v = $v['value'];
                        if ($this->prototype instanceof ArrayNode && ($children = $this->prototype->getChildren()) &&
                            array_key_exists('value', $children)) {
                            $valuePrototype = current($this->valuePrototypes) ?: clone $children['value'];
                            $valuePrototype->parent = $this;
                            $originalClosures = $this->prototype->normalizationClosures;
                            if (is_array($originalClosures)) {
                                $valuePrototypeClosures = $valuePrototype->normalizationClosures;
                                $valuePrototype->normalizationClosures = is_array($valuePrototypeClosures) ?
                                    array_merge($originalClosures, $valuePrototypeClosures) : $originalClosures;
                            }
                            $this->valuePrototypes[$k] = $valuePrototype;
                        }
                    }
                }

                if (array_key_exists($k, $normalized)) {
                    $ex = new DuplicateKeyException(sprintf('Duplicate key "%s" for path "%s".', $k, $this->getPath()));
                    $ex->setPath($this->getPath());

                    throw $ex;
                }
            }

            $prototype = $this->getPrototypeForChild($k);
            if (null !== $this->keyAttribute || $isAssoc) {
                $normalized[$k] = $prototype->normalize($v);
            } else {
                $normalized[] = $prototype->normalize($v);
            }
        }

        return $normalized;
    }

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

    /**
     * Merges values together.
     *
     * @param mixed $leftSide  The left side to merge
     * @param mixed $rightSide The right side to merge
     *
     * @return mixed The merged values
     *
     * @throws InvalidConfigurationException
     * @throws RuntimeException
     */
    protected function mergeValues($leftSide, $rightSide)
    {
        if (false === $rightSide) {
            // if this is still false after the last config has been merged the
            // finalization pass will take care of removing this key entirely
            return false;
        }

        if (false === $leftSide || !$this->performDeepMerging) {
            return $rightSide;
        }

        foreach ($rightSide as $k => $v) {
            // prototype, and key is irrelevant, append the element
//            if (!($prototype instanceof ConstantNode) && null === $this->keyAttribute) {
//                $leftSide[] = $v;
//                continue;
//            }

            // no conflict
            if (!array_key_exists($k, $leftSide)) {
                if (!$this->allowNewKeys) {
                    $ex = new InvalidConfigurationException(sprintf('You are not allowed to define new elements for path "%s". Please define all elements for this path in one config file.', $this->getPath()));
                    $ex->setPath($this->getPath());

                    throw $ex;
                }

                $leftSide[$k] = $v;
                continue;
            }

            $prototype = $this->getPrototypeForChild($k);
            $leftSide[$k] = $prototype->merge($leftSide[$k], $v);
        }

        return $leftSide;
    }
}
