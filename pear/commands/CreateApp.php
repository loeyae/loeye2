<?php

/**
 * CreateApp.php
 *
 * Licensed under the Apache License, Version 2.0 (the "License"),
 * see LICENSE for more details: http://www.apache.org/licenses/LICENSE-2.0.
 *
 * @author  Zhang Yi <loeyae@gmail.com>
 * @version SVN: $Id: Zhang Yi $
 */

namespace loeye\commands;

use loeye\commands\helper\GeneratorUtils;
use loeye\console\Command;
use RuntimeException;
use Symfony\Component\Console\{Input\InputInterface, Output\OutputInterface, Style\SymfonyStyle};
use SmartyException;
use Symfony\Component\Filesystem\Filesystem;

/**
 * CreateApp
 *
 * @author   Zhang Yi <loeyae@gmail.com>
 */
class CreateApp extends Command
{

    protected $name = 'loeye:create-app';
    protected $desc = 'create application';
    protected $args = [
        ['property', 'required' => true, 'help' => 'property name']
    ];
    protected $params = [
        ['path', 'p', 'required' => false, 'help' => 'path', 'default' => null]
    ];
    protected $property;
    protected $dirMap = [
        'app' => [
            'commands' => 'property',
            'conf' => [
                'modules' => 'property',
                'router' => 'general',
                'property' => [
                    'app' => null,
                    'cache' => null,
                    'database' => null,
                ],
                'validate' => 'property',
            ],
            'controllers' => 'property',
            'errors' => null,
            'models' => [
                'entity' => 'property',
                'repository' => 'property',
                'proxy' => null,
                'server' => 'property',
            ],
            'plugins' => 'property',
            'resource' => 'property',
            'views' => 'property',
        ],
        'htdocs' => [
            'static' => [
                'css' => null,
                'js' => null,
                'images' => null,
            ]
        ],
        'runtime' => null,
    ];

    /**
     * process
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return void
     * @throws SmartyException
     */
    public function process(InputInterface $input, OutputInterface $output): void
    {
        $this->property = $input->getArgument('property');
        $ui = new SymfonyStyle($input, $output);
        $dir = $input->getOption('path') ?? getcwd();
        $ui->block($dir);
        $this->mkdir($ui, $dir, $this->dirMap);
        $this->initFile($ui, $dir);
    }


    /**
     *
     * @param SymfonyStyle $ui
     * @param string $base
     * @param mixed $var
     *
     * @return string
     */
    protected function mkdir(SymfonyStyle $ui, string $base, $var): ?string
    {
        $dir = $base;
        if (is_array($var)) {
            foreach ($var as $key => $val) {
                $this->mkdir($ui, $this->mkdir($ui, $base, $key), $val);
            }
        } else {
            if ('property' === $var) {
                $var = $this->property;
            }
            if (null !== $var) {
                $dir .= D_S . $var;
            }
            if (!file_exists($dir)) {
                $ui->block(sprintf('mkdir: %1s', $dir));
                if (!mkdir($dir, 0755, true) && !is_dir($dir)) {
                    throw new RuntimeException(sprintf('Directory "%s" was not created', $dir));
                }
            }
        }
        return $dir;
    }


    /**
     * initFile
     *
     * @param SymfonyStyle $ui
     * @param string $base
     * @return void
     * @throws SmartyException
     */
    protected function initFile(SymfonyStyle $ui, string $base): void
    {
        $fileSystem = new Filesystem();
        $appConfig = $this->buildAppConfigFile($base, 'app');
        $fileSystem->dumpFile($appConfig, $this->replaceProperty('app/AppConfig'));
        $ui->block(sprintf('create file: %1s', $appConfig));
        $dbConfig = $this->buildAppConfigFile($base, 'database');
        $fileSystem->dumpFile($dbConfig, $this->replaceProperty('app/DatabaseConfig'));
        $ui->block(sprintf('create file: %1s', $dbConfig));
        $cacheConfig = $this->buildAppConfigFile($base, 'cache');
        $fileSystem->dumpFile($cacheConfig, $this->replaceProperty('app/CacheConfig'));
        $ui->block(sprintf('create file: %1s', $cacheConfig));
        $moduleConfig = $this->buildConfigFile($base, 'modules');
        $fileSystem->dumpFile($moduleConfig, $this->replaceProperty('app/ModuleConfig'));
        $ui->block(sprintf('create file: %1s', $moduleConfig));
        $routerConfig = $this->buildConfigFile($base, 'router');
        $fileSystem->dumpFile($routerConfig, $this->replaceProperty('app/RouteConfig'));
        $ui->block(sprintf('create file: %1s', $routerConfig));
        $generalErrorFile = GeneratorUtils::buildPath($base, 'app', 'errors', 'GeneralError.php');
        $fileSystem->dumpFile($generalErrorFile, $this->replaceProperty('app/GeneralError'));
        $ui->block(sprintf('create file: %1s', $generalErrorFile));
        $layout = GeneratorUtils::buildPath($base, 'app', 'views', 'layout.tpl');
        $fileSystem->dumpFile($layout, $this->replaceProperty('app/Layout'));
        $ui->block(sprintf('create file: %1s', $layout));
        $home = GeneratorUtils::buildPath($base, 'app', 'views', $this->property, 'home.tpl');
        $fileSystem->dumpFile($home, $this->replaceProperty('app/Home'));
        $ui->block(sprintf('create file: %1s', $home));
        $css = GeneratorUtils::buildPath($base, 'app', 'htdocs', 'static', 'css', 'bootstrap.css');
        $fileSystem->dumpFile($css, $this->replaceProperty('app/BootstrapCSS'));
        $ui->block(sprintf('create file: %1s', $css));
        $htaccess = GeneratorUtils::buildPath($base, 'app', 'htdocs', '.htaccess');
        $fileSystem->dumpFile($htaccess, $this->replaceProperty('app/Htaccess'));
        $ui->block(sprintf('create file: %1s', $htaccess));
        $dispatcher = GeneratorUtils::buildPath($base, 'app', 'htdocs', 'Dispatcher.php');
        $fileSystem->dumpFile($dispatcher, $this->replaceProperty('app/Dispatcher'));
        $ui->block(sprintf('create file: %1s', $dispatcher));
    }


    /**
     * buildAppConfigFile
     *
     * @param string $base
     * @param string $type
     *
     * @return string
     */
    protected function buildAppConfigFile(string $base, string $type): string
    {
        return GeneratorUtils::buildPath($base, 'app', 'conf', $this->property, $type, 'master.yml');
    }


    /**
     * buildConfigFile
     *
     * @param string $base
     * @param string $type
     *
     * @return string
     */
    protected function buildConfigFile(string $base, string $type): string
    {
        return GeneratorUtils::buildPath($base, 'app', 'conf', $type, $this->property, 'master.yml');
    }

    /**
     * replaceProperty
     *
     * @param string $tpl
     *
     * @return string
     * @throws SmartyException
     */
    protected function replaceProperty(string $tpl): string
    {
        return GeneratorUtils::getCodeFromTemplate($tpl, ['property' => $this->property]);
    }

}
