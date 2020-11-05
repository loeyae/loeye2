<?php
/**
 * GenerateClientPlugins.php
 *
 * PHP version 7
 *
 * Licensed under the Apache License, Version 2.0 (the "License"),
 * see LICENSE for more details: http://www.apache.org/licenses/LICENSE-2.0.
 *
 * @category PHP
 * @package  LOEYE
 * @author   Zhang Yi <loeyae@gmail.com>
 * @version  2020/4/15 10:45
 * @link     https://github.com/loeyae/loeye2.git
 */

namespace loeye\commands;

use FilesystemIterator;
use loeye\client\Client;
use loeye\commands\helper\GeneratorUtils;
use loeye\console\Command;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use ReflectionClass;
use ReflectionException;
use ReflectionMethod;
use SmartyException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Class GenerateClientPlugins
 *
 * @package loeye\commands
 */
class GenerateClientPlugins extends Command
{

    protected $name = 'loeye:generate-client-plugins';
    protected $desc = 'generate plugin with client';
    protected $args = [
        ['property', 'required' => true, 'help' => 'The application property name.']
    ];
    protected $params = [
        ['filter', 'f', 'required' => false, 'help' => 'filter', 'default' => null],
        ['force', null, 'required' => false, 'help' => 'force update file', 'default' => false],
    ];

    protected static $_statement = <<<'EOF'
        $<{$param}> = Utils::getData($context, '<{$className}>_<{$param}>');
EOF;

    protected static $_firstStatement = <<<'EOF'
        $<{$param}> = Utils::getData($context, '<{$className}>_input');
EOF;


    /**
     * @inheritDoc
     * @throws SmartyException
     * @throws ReflectionException
     */
    public function process(InputInterface $input, OutputInterface $output)
    {
        $ui = new SymfonyStyle($input, $output);
        $property = $input->getArgument('property');
        $force = $input->getOption('force');
        $dir = realpath(PROJECT_DIR . D_S . 'services' . D_S . 'client' . D_S . $property);
        if (!$dir) {
            $ui->error('Clients of Property: ' . $property . ' not exists');
        }
        $clientNamespace = GeneratorUtils::getNamespace($dir);
        $destDir = $this->getDestDir($property);
        foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS)) as $file) {
            if ($file->isFile() && $file->getExtension() === 'php') {
                $clientName = $file->getBaseName('.php');
                $fullClientName = '\\' . $clientNamespace . '\\' . $clientName;
                $reflection = new ReflectionClass($fullClientName);
                if (!$reflection->isSubclassOf(Client::class)) {
                    continue;
                }
                $targetDir = $destDir .D_S. lcfirst(substr($clientName, 0, -6));
                $namespace = GeneratorUtils::getNamespace($targetDir);
                $abstractClassName = 'AbstractBasePlugin';
                $this->generateAbstractPlugin($ui, $namespace, $abstractClassName, $fullClientName, $targetDir, $force);
                $this->generatePlugins($ui, $reflection, $namespace, $abstractClassName, $targetDir, $force);
            }
        }

        $ui->newLine();
        $ui->text(sprintf('classes generated to "<info>%s</info>"', $destDir));
    }

    /**
     * @param SymfonyStyle $ui
     * @param $namespace
     * @param $className
     * @param $clientName
     * @param $destDir
     * @param $force
     * @throws SmartyException
     */
    protected function generateAbstractPlugin(SymfonyStyle $ui, $namespace, $className, $clientName, $destDir, $force): void
    {
        $variable = [
            'className' => $className,
            'namespace' => $namespace,
            'fullClientName' => $clientName,
            'clientName' => GeneratorUtils::getClassName($clientName),
        ];
        $fullClassName = GeneratorUtils::generateClassName($namespace, $className);
        $ui->text(sprintf('Processing AbstractPluginFile "<info>%s</info>"', $fullClassName));
        $code = GeneratorUtils::getCodeFromTemplate('client/AbstractBasePlugin', $variable);
        GeneratorUtils::writeFile($destDir, $className, $code, $force);
    }

    /**
     * @param SymfonyStyle $ui
     * @param ReflectionMethod $method
     * @param $namespace
     * @param $className
     * @param $abstractClassName
     * @param $destDir
     * @param $force
     * @throws SmartyException
     */
    protected function generatePlugin(SymfonyStyle $ui, ReflectionMethod $method, $namespace, $className,
                                      $abstractClassName, $destDir, $force): void
    {
        [$parameterStatement, $parameter] = $this->generateParameter($className, $method);
        $variable = [
            'className' => $className,
            'namespace' => $namespace,
            'method' => $method->getName(),
            'abstractClassName' => $abstractClassName,
            'parameterStatement' => $parameterStatement,
            'parameter' => $parameter,
        ];
        $fullClassName = GeneratorUtils::generateClassName($namespace, $className);
        sprintf('Processing PluginFile "<info>%s</info>"', $fullClassName);
        $code = GeneratorUtils::getCodeFromTemplate('client/Plugin', $variable);
        GeneratorUtils::writeFile($destDir, $className, $code, $force);
    }

    /**
     * generateParameter
     *
     * @param $className
     * @param ReflectionMethod $method
     * @return array
     */
    private function generateParameter($className, ReflectionMethod $method): array
    {
        $params = $method->getParameters();
        if ($params) {
            $content = [];
            $parameter = [];
            $first = true;
            foreach ($params as $param) {
                if ($param->getName() === 'ret') {
                    continue;
                }
                $parameter[] = '$' . $param->getName();
                $variables = [
                    'param' => $param->getName(),
                    'className' => $className,
                ];
                if ($first) {
                    $content[] = GeneratorUtils::generateCodeByTemplate($variables, self::$_firstStatement);
                    $first = false;
                } else {
                    $content[] = GeneratorUtils::generateCodeByTemplate($variables, self::$_statement);
                }
            }
            return [implode("\r\n", $content), implode(', ', $parameter)];
        }
        return ['', ''];
    }

    /**
     * getDestDir
     *
     * @param string|null $property
     * @return bool|string
     */
    private function getDestDir(?string $property)
    {
        $path = PROJECT_DIR . D_S . 'services' . D_S . 'plugins' . D_S . $property;
        if (!file_exists($path)) {
            $fileSystem = new Filesystem();
            $fileSystem->mkdir($path);
        }
        return realpath($path);
    }

    /**
     * generatePlugins
     *
     * @param SymfonyStyle $ui
     * @param ReflectionClass $reflection
     * @param string $namespace
     * @param string $abstractClassName
     * @param string $targetDir
     * @param bool|null $force
     * @throws SmartyException
     */
    private function generatePlugins(SymfonyStyle $ui, ReflectionClass $reflection, string $namespace, string $abstractClassName, string $targetDir, ?bool $force): void
    {
        $methods = $reflection->getMethods();
        foreach ($methods as $method) {
            $name = $method->getName();
            if (in_array($name, ['all', 'delete', 'get', 'insert', 'one', 'page', 'update'])) {
                $className = ucfirst($name) .'Plugin';
                $fullClassName = GeneratorUtils::generateClassName($namespace, $className);
                $ui->text(sprintf('Processing PluginFile "<info>%s</info>"', $fullClassName));
                $this->generatePlugin($ui, $method, $namespace, $className, $abstractClassName, $targetDir, $force);
            }
        }
    }

}