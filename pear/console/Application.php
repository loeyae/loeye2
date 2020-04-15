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
 * @version  2018-07-23 22:44:28
 * @link     https://github.com/loeyae/loeye2.git
 */

namespace loeye\console;

use FilesystemIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use ReflectionException;
use Symfony\Component\Console\Application as Base;

/**
 * Application
 *
 * @author   Zhang Yi <loeyae@gmail.com>
 */
class Application extends Base
{

    public const DN = 'commands';

    public function __construct(string $name = 'UNKNOWN', string $version = 'UNKNOWN')
    {
        parent::__construct($name, $version);
        $this->loadCommand();
    }

    /**
     * loadCommand
     *
     * @return void
     * @throws ReflectionException
     */
    protected function loadCommand(): void
    {
        $loeyeCommandsDir = realpath(LOEYE_DIR . DIRECTORY_SEPARATOR . self::DN);
        $loeyeNS          = '\\loeye\\' . self::DN;
        $this->loadCommandByDir($loeyeCommandsDir, $loeyeNS);
        $appCommandsDir   = realpath(PROJECT_DIR . DIRECTORY_SEPARATOR . self::DN);
        if ($appCommandsDir) {
            $appNS = '\\' . PROJECT_NAMESPACE . '\\' . self::DN;
            $this->loadCommandByDir($appCommandsDir, $appNS);
        }
    }

    /**
     * loadCommandByDir
     *
     * @param string $dir
     * @param string $ns
     * @return void
     * @throws ReflectionException
     */
    protected function loadCommandByDir($dir, $ns): void
    {
        foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS)) as $file) {
            if ($file->isFile() && $file->getExtension() === 'php') {
                $path = $file->getPath();
                if ($path !== $ns) {
                    $_ns = str_replace(array($dir, '/'), array($ns, '\\'), $path);
                    $cn  = $_ns . '\\' . $file->getBasename('.' . $file->getExtension());
                } else {
                    $cn = $ns . '\\' . $file->getBasename('.' . $file->getExtension());
                }
                $reflection = new \ReflectionClass($cn);
                if ($reflection->isSubclassOf(Command::class)) {
                    $this->add($reflection->newInstanceArgs());
                }
            }
        }
    }

}
