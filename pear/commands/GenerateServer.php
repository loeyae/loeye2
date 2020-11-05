<?php

/**
 * GenerateServer.php
 *
 * Licensed under the Apache License, Version 2.0 (the "License"),
 * see LICENSE for more details: http://www.apache.org/licenses/LICENSE-2.0.
 *
 * @author  Zhang Yi <loeyae@gmail.com>
 * @version SVN: $Id: Zhang Yi $
 */

namespace loeye\commands;

use Doctrine\Persistence\Mapping\ClassMetadata;
use loeye\commands\helper\EntityGeneratorTrait;
use loeye\commands\helper\GeneratorUtils;
use loeye\console\Command;
use SmartyException;
use Symfony\Component\Console\{Input\InputInterface, Style\SymfonyStyle};

/**
 * GenerateServer
 *
 * @author   Zhang Yi <loeyae@gmail.com>
 */
class GenerateServer extends Command
{

    use EntityGeneratorTrait;

    protected $args = [
        ['property', 'required' => true, 'help' => 'The application property name.']
    ];
    protected $params = [
        ['db-id', 'd', 'required' => false, 'help' => 'database setting id', 'default' => 'default'],
        ['filter', 'f', 'required' => false, 'help' => 'filter', 'default' => null],
        ['force', null, 'required' => false, 'help' => 'force update file', 'default' => false],
    ];
    protected $name = 'loeye:generate-server';
    protected $desc = 'generate server with entity';


    /**
     * generateFile
     *
     * @param SymfonyStyle $ui
     * @param ClassMetadata $metadata
     * @param string $namespace
     * @param string $destPath
     * @param boolean $force
     * @throws SmartyException
     */
    protected function generateFile(SymfonyStyle $ui, ClassMetadata $metadata, $namespace, $destPath, $force): void
    {
        $className = $this->getClassName($metadata->reflClass->name);
        $fullClassName = GeneratorUtils::generateClassName($namespace, $className);
        $ui->text(sprintf('Processing Server "<info>%s</info>"', $fullClassName));
        $this->writeServerClass($namespace, $className, $metadata->reflClass->name, $destPath, $force);
    }


    /**
     *
     * @param InputInterface $input
     *
     * @param SymfonyStyle $ui
     * @return string
     */
    protected function getDestPath(InputInterface $input, SymfonyStyle $ui): string
    {
        return PROJECT_MODELS_DIR . D_S . 'server' . D_S . $input->getArgument('property');
    }

    /**
     * getClassName
     *
     * @param string $fullClassName
     * @return string
     */
    protected function getClassName($fullClassName): string
    {
        return GeneratorUtils::getClassName($fullClassName) . 'Server';
    }


    /**
     * generateServerClass
     *
     * @param string $namespace
     * @param string $className
     * @param string $entityClass
     * @return string
     * @throws SmartyException
     */
    protected function generateServerClass($namespace, $className, $entityClass): string
    {
        $variables = [
            'namespace' => $namespace,
            'className' => $className,
            'fullEntityClass' => $entityClass,
            'entityClass' => GeneratorUtils::getClassName($entityClass),
        ];

        return GeneratorUtils::getCodeFromTemplate('server/Server', $variables);
    }


    /**
     * writeServerClass
     *
     * @param string $namespace
     * @param string $className
     * @param string $entityClass
     * @param string $outputDirectory
     * @param boolean $force
     * @throws SmartyException
     */
    public function writeServerClass($namespace, $className, $entityClass, $outputDirectory, $force = false): void
    {
        $code = $this->generateServerClass($namespace, $className, $entityClass);

        GeneratorUtils::writeFile($outputDirectory, $className, $code, $force);
    }

}
