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
 * @version  2018-07-23 22:44:28
 * @link     https://github.com/loeyae/loeye2.git
 */

namespace loeye\plugin;

use loeye\base\Context;
use loeye\base\Utils;
use loeye\std\Plugin;
use loeye\web\Template;
use Smarty;
use SmartyException;

/**
 * SectionViewPlugin
 *
 * @author   Zhang Yi <loeyae@gmail.com>
 */
class SectionViewPlugin extends Plugin
{

    /**
     * process
     *
     * @param Context $context context
     * @param array $inputs inputs
     *
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @throws SmartyException
     */
    public function process(Context $context, array $inputs): void
    {
        $sections = Utils::checkNotEmpty($inputs, 'sections');
        $context->set('view_section', $sections);
        if ($context->getTemplate() instanceof Template) {
            $context->getTemplate()->smarty()->setCompileId($context->getRequest()->getModuleId());
            if ($context->getTemplate()->smarty()->caching !== Smarty::CACHING_OFF && !($context->getTemplate()
                    ->smarty()->isCached())) {
                $context->getTemplate()->smarty()->setCacheId($context->getRequest()->getModuleId());
            }
        }
    }

}
