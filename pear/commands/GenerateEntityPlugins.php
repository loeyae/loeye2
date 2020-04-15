<?php

/**
 * GenerateEntityPlugins.php
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
use ReflectionClass;
use ReflectionException;
use ReflectionMethod;
use ReflectionParameter;
use SmartyException;
use Symfony\Component\Console\{Input\InputInterface, Style\SymfonyStyle};

/**
 * GenerateEntityPlugins
 *
 * @author   Zhang Yi <loeyae@gmail.com>
 */
class GenerateEntityPlugins extends Command
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
    protected $name = 'loeye:generate-entity-plugins';
    protected $desc = 'generate plugin with entity';

    private static $_statement = <<<'EOF'
        $<{$param}> = Utils::getData($context, '<{$className}>_<{$param}>');
EOF;

    private static $_firstStatement = <<<'EOF'
        $<{$param}> = Utils::getData($context, '<{$className}>_input');
EOF;

    /**
     * generateFile
     *
     * @param SymfonyStyle $ui
     * @param ClassMetadata $metadata
     * @param string $namespace
     * @param string $destPath
     * @param boolean $force
     * @throws ReflectionException
     * @throws SmartyException
     */
    protected function generateFile(SymfonyStyle $ui, ClassMetadata $metadata, $namespace, $destPath, $force): void
    {
        $entityName = GeneratorUtils::getClassName($metadata->reflClass->name);
        $namespace .= '\\' . lcfirst($entityName);
        $destPath .= D_S . lcfirst($entityName);
        $abstractClassName = 'Abstract' . ucfirst($entityName) . 'BasePlugin';
        $serverClass = $this->getServerClass($metadata->reflClass->name);
        $this->writeAbstractPluginClass($ui, $namespace, $abstractClassName, $serverClass, $destPath, $force);
        $this->writePluginClass($ui, $namespace, $entityName, $abstractClassName, $serverClass, $destPath, $force);
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
        return PROJECT_DIR . D_S . 'plugins' . D_S . $input->getArgument('property');
    }

    /**
     * getServerClass
     *
     * @param string $className
     * @return string
     */
    protected function getServerClass($className): string
    {
        return '\\' . str_replace('entity', 'server', $className) . 'Server';
    }

    /**
     * generateAbstractPluginClass
     *
     * @param string $namespace
     * @param string $className
     * @param $serverClass
     * @return string
     * @throws SmartyException
     */
    protected function generateAbstractPluginClass($namespace, $className, $serverClass): string
    {
        $variables = [
            'namespace' => $namespace,
            'fullServerClass' => $serverClass,
            'serverClass' => GeneratorUtils::getClassName($serverClass),
            'className' => $className,
        ];

        return GeneratorUtils::getCodeFromTemplate('entity/AbstractBasePlugin', $variables);
    }

    /**
     * generatePluginClass
     *
     * @param string $namespace
     * @param string $className
     * @param $abstractClassName
     * @param $method
     * @param $paramsStatement
     * @param $params
     * @param $returnType
     * @return string
     * @throws SmartyException
     */
    protected function generatePluginClass($namespace, $className, $abstractClassName, $method, $paramsStatement, $params, $returnType): string
    {
        $variables = [
            'namespace' => $namespace,
            'className' => $className,
            'abstractClassName' => $abstractClassName,
            'method' => $method,
            'paramsStatement' => $paramsStatement,
            'params' => $params,
            'returnType' => $returnType,
        ];

        return GeneratorUtils::getCodeFromTemplate('entity/Plugin', $variables);
    }

    /**
     * writeAbstractPluginClass
     *
     * @param SymfonyStyle $ui
     * @param string $namespace
     * @param string $className
     * @param $serverClass
     * @param string $outputDirectory
     * @param boolean $force
     * @throws SmartyException
     */
    public function writeAbstractPluginClass(SymfonyStyle $ui, $namespace, $className, $serverClass, $outputDirectory,
                                             $force = false): void
    {
        $fullAbstractClassName = $namespace . '\\' . $className;
        $ui->text(sprintf('Processing AbstractPlugin "<info>%s</info>"', $fullAbstractClassName));
        $code = $this->generateAbstractPluginClass($namespace, $className, $serverClass);

        GeneratorUtils::writeFile($outputDirectory, $className, $code, $force);
    }

    /**
     * write plugin class
     *
     * @param SymfonyStyle $ui
     * @param string $namespace
     * @param string $className
     * @param string $abstractClassName
     * @param string $serverClass
     * @param string $outputDirectory
     * @param bool $force
     * @throws ReflectionException
     * @throws SmartyException
     */
    public function writePluginClass(SymfonyStyle $ui, $namespace, $className, $abstractClassName, $serverClass, $outputDirectory, $force = false): void
    {
        $refClass = new ReflectionClass($serverClass);
        $methods = $refClass->getMethods();
        foreach ($methods as $method) {
            if ($method->isConstructor() || $method->isFinal() || $method->isPrivate()) {
                continue;
            }
            $methodName = $method->getName();
            $returnType = $method->getReturnType();
            if ($returnType == 'loeye\database\Entity') {
                $returnType = str_replace('server', 'entity', substr($serverClass, 0, -6));
            }
            $nClassName = ucfirst($className) . ucfirst($methodName) . 'Plugin';

            $fullClassName = $namespace . '\\' . $nClassName;
            $ui->text(sprintf('Processing Plugin "<info>%s</info>"', $fullClassName));
            $paramsStatement = $this->generateParamsStatement($method, $nClassName);
            $params = $this->generateParams($method);
            $code = $this->generatePluginClass($namespace, $nClassName, $abstractClassName, $methodName, $paramsStatement, $params, $returnType);

            GeneratorUtils::writeFile($outputDirectory, $nClassName, $code, $force);
        }
    }

    /**
     * generate params statement
     *
     * @param ReflectionMethod $method
     * @param string $className
     * @return string
     */
    protected function generateParamsStatement(ReflectionMethod $method, $className): string
    {
        $params = $method->getParameters();
        if ($params) {
            $content = [];
            $first = true;
            foreach ($params as $param) {
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
            return implode("\r\n", $content);
        }
        return '';
    }

    /**
     * generate params
     *
     * @param ReflectionMethod $method
     * @return string
     */
    protected function generateParams(ReflectionMethod $method): string
    {
        $params = $method->getParameters();
        $initial = '';
        if ($params) {
            return array_reduce($params, static function ($carry, ReflectionParameter $item) {
                if ($carry) {
                    $carry .= ', $' . $item->getName();
                } else {
                    $carry = '$' . $item->getName();
                }
                return $carry;
            }, $initial);
        }
        return $initial;
    }

}
