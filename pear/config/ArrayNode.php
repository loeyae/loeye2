<?php

/**
 * ArrayNode.php
 *
 * PHP version 7
 *
 * Licensed under the Apache License, Version 2.0 (the "License"),
 * see LICENSE for more details: http://www.apache.org/licenses/LICENSE-2.0.
 *
 * @category PHP
 * @package  LOEYE
 * @author   Zhang Yi <loeyae@gmail.com>
 * @version  2020年1月15日 下午10:37:53
 * @link     https://github.com/loeyae/loeye.git
 */

namespace loeye\config;

use \Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use \Symfony\Component\Config\Definition\Exception\UnsetKeyException;

/**
 * ArrayNode
 *
 * @author   Zhang Yi <loeyae@gmail.com>
 */
class ArrayNode extends \Symfony\Component\Config\Definition\ArrayNode {


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

        $value = $this->remapXml($value);

        $normalized = [];
        $regexNode = $this->getRegexNode();
        foreach ($value as $name => $val) {
            if (isset($this->children[$name])) {
                try {
                    $normalized[$name] = $this->children[$name]->normalize($val);
                } catch (UnsetKeyException $e) {

                }
                unset($value[$name]);
            } else {
                foreach ($regexNode as $node) {
                    if (is_array($val) && $node instanceof PrototypedRegexNode) {
                        try {
                            $normalized[$name] = $node->normalize($val);
                        } catch (UnsetKeyException $e) {

                        } catch (\Exception $e) {
                            continue;
                        }
                        unset($value[$name]);
                        goto brk;
                    } else if ($node instanceof RegexNode) {
                        if ($node->match($name)) {
                            if (is_array($val)) {
                                try {
                                    $normalized[$name] = $node->normalize($val);
                                } catch (UnsetKeyException $e) {

                                } catch (\Exception $e) {
                                    continue;
                                }
                            } else {
                                $normalized[$name] = $val;
                            }
                            unset($value[$name]);
                            goto brk;
                        }
                    }
                }
                if (!$this->removeExtraKeys) {
                    $normalized[$name] = $val;
                }
                brk:
            }
        }

        // if extra fields are present, throw exception
        if (\count($value) && !$this->ignoreExtraKeys) {
            $proposals = array_keys($this->children);
            sort($proposals);
            $guesses   = [];

            foreach (array_keys($value) as $subject) {
                $minScore = INF;
                foreach ($proposals as $proposal) {
                    $distance = levenshtein($subject, $proposal);
                    if ($distance <= $minScore && $distance < 3) {
                        $guesses[$proposal] = $distance;
                        $minScore           = $distance;
                    }
                }
            }

            $msg = sprintf('Unrecognized option%s "%s" under "%s"', 1 === \count($value) ? '' : 's', implode(', ', array_keys($value)), $this->getPath());

            if (\count($guesses)) {
                asort($guesses);
                $msg .= sprintf('. Did you mean "%s"?', implode('", "', array_keys($guesses)));
            } else {
                $msg .= sprintf('. Available option%s %s "%s".', 1 === \count($proposals) ? '' : 's', 1 === \count($proposals) ? 'is' : 'are', implode('", "', $proposals));
            }

            $ex = new InvalidConfigurationException($msg);
            $ex->setPath($this->getPath());

            throw $ex;
        }

        return $normalized;
    }

    /**
     * getRegexNode
     * 
     * @return array
     */
    private function getRegexNode()
    {
        return array_filter($this->children, function($item){
            if ($item instanceof RegexNode || $item instanceof PrototypedRegexNode) {
                return $item;
            }
            return null;
        });

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
     * @throws \RuntimeException
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
            // no conflict
            if (!\array_key_exists($k, $leftSide)) {
                if (!$this->allowNewKeys) {
                    $ex = new InvalidConfigurationException(sprintf('You are not allowed to define new elements for path "%s". Please define all elements for this path in one config file. If you are trying to overwrite an element, make sure you redefine it with the same name.', $this->getPath()));
                    $ex->setPath($this->getPath());

                    throw $ex;
                }

                $leftSide[$k] = $v;
                continue;
            }

            if (!isset($this->children[$k])) {
                $node = current($this->children);
                if (!($node instanceof RegexNode) && (!$this->ignoreExtraKeys || $this->removeExtraKeys)) {
                    throw new \RuntimeException('merge() expects a normalized config array.');
                }

                $leftSide[$k] = $v;
                continue;
            }

            $leftSide[$k] = $this->children[$k]->merge($leftSide[$k], $v);
        }

        return $leftSide;
    }

}
