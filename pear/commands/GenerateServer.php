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
use loeye\console\Command;
use Symfony\Component\Console\{Input\InputInterface, Output\OutputInterface, Style\SymfonyStyle};
use loeye\console\helper\EntityGeneratorTrait;
use loeye\database\Server;

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
    protected static $_template = '<?php

namespace <namespace>;

/**
 * <className>
 *
 * This class was generated by the loeye2. Add your own custom
 * server methods below.
 */
class <className> extends <serverName>
{
    protected $entityClass = <entityClass>;
}
';


    /**
     * generateFile
     *
     * @param SymfonyStyle $ui
     * @param ClassMetadata $metadata
     * @param string $namespace
     * @param string $destPath
     * @param boolean $force
     */
    protected function generateFile(SymfonyStyle $ui, ClassMetadata $metadata, $namespace, $destPath, $force): void
    {
        $className = $this->getClassName($metadata->reflClass->name);
        $entityClass = $this->getEntityClass($metadata->reflClass->name);
        $fullClassName = $namespace . '\\' . $className;
        $ui->text(sprintf('Processing Server "<info>%s</info>"', $fullClassName));
        $this->writeServerClass($namespace, $className, $entityClass, $destPath, $force);
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
     * getEntityClass
     *
     * @param string $className
     * @return string
     */
    protected function getEntityClass($className): string
    {
        return '\\' . $className . '::class';
    }


    /**
     * getClassName
     *
     * @param string $fullClassName
     * @return string
     */
    protected function getClassName($fullClassName): string
    {
        return substr($fullClassName, strrpos($fullClassName, '\\') + 1) . 'Server';
    }


    /**
     * generateServerClass
     *
     * @param string $namespace
     * @param string $className
     * @param string $entityClass
     * @return string
     */
    protected function generateServerClass($namespace, $className, $entityClass): string
    {
        $variables = [
            '<namespace>' => $namespace,
            '<serverName>' => Server::class,
            '<className>' => $className,
            '<entityClass>' => $entityClass,
        ];

        return str_replace(array_keys($variables), array_values($variables), self::$_template);
    }


    /**
     * writeServerClass
     *
     * @param string $namespace
     * @param string $className
     * @prara String  $entityClass
     * @param string $outputDirectory
     * @param boolean $force
     */
    public function writeServerClass($namespace, $className, $entityClass, $outputDirectory, $force = false): void
    {
        $code = $this->generateServerClass($namespace, $className, $entityClass);

        $this->writeFile($outputDirectory, $className, $code, $force);
    }

}
