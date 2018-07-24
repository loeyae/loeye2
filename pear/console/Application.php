<?php

/**
 * Application.php
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

namespace loeye\console;

use Symfony\Component\Console\Application as Base;

/**
 * Application
 *
 * @author   Zhang Yi <loeyae@gmail.com>
 */
class Application extends Base
{

    const DN = 'commands';

    public function __construct(string $name = 'UNKNOWN', string $version = 'UNKNOWN')
    {
        parent::__construct($name, $version);
        $this->loadCommand();
    }

    protected function loadCommand()
    {
        $commandsDir = realpath(PROJECT_DIR . DIRECTORY_SEPARATOR . self::DN);
        $ns          = '\\' . PROJECT_NAMESPACE . '\\' . self::DN;
        foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($commandsDir, \FilesystemIterator::SKIP_DOTS)) as $file) {
            if ($file->isFile()) {
                $cn = $ns . '\\' . $file->getBasename('.' . $file->getExtension());
                $this->add(new $cn());
            }
        }
    }

}