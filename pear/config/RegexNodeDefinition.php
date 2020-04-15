<?php

/**
 * RegexNodeDefinition.php
 *
 * PHP version 7
 * 
 * Licensed under the Apache License, Version 2.0 (the "License"),
 * see LICENSE for more details: http://www.apache.org/licenses/LICENSE-2.0.
 * 
 * @category PHP
 * @package  LOEYE
 * @author   Zhang Yi <loeyae@gmail.com>
 * @version  2020年1月15日 下午9:52:01
 * @link     https://github.com/loeyae/loeye.git
 */

namespace loeye\config;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;

/**
 * RegexNodeDefinition
 *
 * @author   Zhang Yi <loeyae@gmail.com>
 */
class RegexNodeDefinition extends ArrayNodeDefinition {

    /**
     * @return RegexNodeDefinition
     */
    public function regexPrototype(): RegexNodeDefinition
    {
        return $this->prototype('regex');
    }
    
    
    /**
     * {@inheritdoc}
     */
    protected function createNode()
    {
        if (null === $this->prototype) {
            $node = new RegexNode($this->name, $this->parent, $this->pathSeparator);

            $this->validateConcreteNode($node);

            $node->setAddIfNotSet($this->addDefaults);

            foreach ($this->children as $child) {
                $child->parent = $node;
                $node->addChild($child->getNode());
            }
        } else {
            $node = new PrototypedRegexNode($this->name, $this->parent, $this->pathSeparator);

            $this->validatePrototypeNode($node);

            if (null !== $this->key) {
                $node->setKeyAttribute($this->key, $this->removeKeyItem);
            }

            if (true === $this->atLeastOne || false === $this->allowEmptyValue) {
                $node->setMinNumberOfElements(1);
            }

            if ($this->default) {
                $node->setDefaultValue($this->defaultValue);
            }

            if (false !== $this->addDefaultChildren) {
                $node->setAddChildrenIfNoneSet($this->addDefaultChildren);
                if ($this->prototype instanceof static && null === $this->prototype->prototype) {
                    $this->prototype->addDefaultsIfNotSet();
                }
            }

            $this->prototype->parent = $node;
            $node->setPrototype($this->prototype->getNode());
        }

        $node->setAllowNewKeys($this->allowNewKeys);
        $node->addEquivalentValue(null, $this->nullEquivalent);
        $node->addEquivalentValue(true, $this->trueEquivalent);
        $node->addEquivalentValue(false, $this->falseEquivalent);
        $node->setPerformDeepMerging($this->performDeepMerging);
        $node->setRequired($this->required);
        $node->setDeprecated($this->deprecationMessage);
        $node->setIgnoreExtraKeys($this->ignoreExtraKeys, $this->removeExtraKeys);
        $node->setNormalizeKeys($this->normalizeKeys);

        if (null !== $this->normalization) {
            $node->setNormalizationClosures($this->normalization->before);
            $node->setXmlRemappings($this->normalization->remappings);
        }

        if (null !== $this->merge) {
            $node->setAllowOverwrite($this->merge->allowOverwrite);
            $node->setAllowFalse($this->merge->allowFalse);
        }

        if (null !== $this->validation) {
            $node->setFinalValidationClosures($this->validation->rules);
        }

        return $node;
    }

}
