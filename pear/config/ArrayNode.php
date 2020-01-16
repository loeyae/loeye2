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
        foreach ($value as $name => $val) {
            if (isset($this->children[$name])) {
                try {
                    $normalized[$name] = $this->children[$name]->normalize($val);
                } catch (UnsetKeyException $e) {

                }
                unset($value[$name]);
            } else {
                foreach ($this->children as $key => $node) {
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

}
