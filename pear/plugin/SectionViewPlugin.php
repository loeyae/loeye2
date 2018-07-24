<?php

/**
 * SectionViewPlugin.php
 *
 * PHP version 7
 *
 * Licensed under the Apache License, Version 2.0 (the "License"),
 * see LICENSE for more details: http://www.apache.org/licenses/LICENSE-2.0.
 *
 * @category PHP
 * @package  LOEYE
 * @author   Zhang Yi <loeyae@gmail.com>
 * @version  GIT: $Id: Zhang Yi $
 * @link     https://github.com/loeyae/loeye.git
 */

namespace loeye\plugin;

/**
 * SectionViewPlugin
 *
 * @author   Zhang Yi <loeyae@gmail.com>
 */
class SectionViewPlugin extends \loeye\std\Plugin
{

    /**
     * process
     *
     * @param \loeye\base\Context $context context
     * @param array               $inputs  inputs
     *
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function process(\loeye\base\Context $context, array $inputs)
    {
        $sections = \loeye\base\Utils::checkNotEmpty($inputs, 'sections');
        $context->set('view_section', $sections);
        if ($context->getTemplate() instanceof Template) {
            $context->getTemplate()->smarty()->setCompileId($context->getRequest()->getModuleId());
            if ($context->getTemplate()->smarty()->caching != Smarty::CACHING_OFF && !($context->getTemplate()->smarty()->getCacheId())) {
                $context->getTemplate()->smarty()->setCacheId($context->getRequest()->getModuleId());
            }
        }
    }

}
