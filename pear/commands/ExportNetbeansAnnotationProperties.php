<?php

/**
 * ExportNetbeansAnnotationProperties.php
 *
 * Licensed under the Apache License, Version 2.0 (the "License"),
 * see LICENSE for more details: http://www.apache.org/licenses/LICENSE-2.0.
 *
 * @author  Zhang Yi <loeyae@gmail.com>
 * @version SVN: $Id: Zhang Yi $
 */

namespace loeye\commands;

use Doctrine\Common\Annotations\Annotation\Target;
use Doctrine\Common\Annotations\DocParser;
use Doctrine\ORM\Mapping\Annotation;
use FilesystemIterator;
use IteratorIterator;
use loeye\console\Command;
use ReflectionClass;
use SplFileInfo;
use Symfony\Component\Console\{Input\InputInterface, Output\OutputInterface};
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Validator\Constraint;

/**
 * ExportNetbeansAnnotationProperties
 *
 * @author   Zhang Yi <loeyae@gmail.com>
 */
class ExportNetbeansAnnotationProperties extends Command {

    private $index                     = 0;
    protected $desc                    = 'export annotation.propertyies for netbeans';
    protected $name                    = 'loeye:export-nb-ap';
    protected $args                    = [
        ['file', 'required' => true, 'help' => 'file name', 'default' => null]
    ];
    protected $params                  = [
        ['short', 's', 'required' => false, 'help' => 'namespace use short name', 'default' => false]
    ];
    protected $template                = <<<'EOF'
tag.{index}.documentation=<p style="font-weight: bold; font-size: 1.2em">@{annotation}</p>\r\n<p style="font-weight: bold; font-size: 1.1em">Description</p>\r\n<p>This <code>@{annotation}</code> annotation shows how to properly write documentation.</p>\r\n<p style="font-weight: bold; font-size: 1.1em">Example</p>\r\n<pre><code>\r\n/**\r\n * @{annotation}({params})\r\n */\r\nprivate $createTime;\r\n</code></pre>\r\n
tag.{index}.insertTemplate=@{annotation}({params})
tag.{index}.name={annotation}
tag.{index}.types={types}
EOF;
    protected $mapping                 = [
        'ORM' => [
            'namespace' => 'Doctrine\ORM\Mapping',
            'root'      => 'doctrine\orm\lib',
            'instance'  => Annotation::class,
            'short'     => 'ORM',
        ],
        'Gedmo' => [
            'namespace' => 'Gedmo\Mapping\Annotation',
            'root'      => 'gedmo\doctrine-extensions\lib',
            'instance'  => \Doctrine\Common\Annotations\Annotation::class,
            'short'     => 'Gedmo',
        ],
        'Assert' => [
            'namespace' => 'Symfony\Component\Validator\Constraints',
            'root'      => 'symfony\validator\Constraints',
            'instance'  => Constraint::class,
            'short'     => 'Assert',
        ],
    ];
    private static $globalImports      = [
        'ignoreannotation' => 'Doctrine\Common\Annotations\Annotation\IgnoreAnnotation',
        'target'           => 'Doctrine\Common\Annotations\Annotation\Target',
    ];
    private static $globalIgnoredNames = [
        'GroupSequence'              => true,
        'Gedmo\Slug'                 => true,
        'Gedmo\SlugHandler'          => true,
        'Gedmo\SlugHandlerOption'    => true,
        // Annotation tags
        'Annotation'                 => true,
        'Attribute'                  => true, 'Attributes'                 => true,
        /* Can we enable this? 'Enum' => true, */
        'Required'                   => true,
//        'Target'                     => false,
        // Widely used tags (but not existent in phpdoc)
        'fix'                        => true, 'fixme'                      => true,
        'override'                   => true,
        // PHPDocumentor 1 tags
        'abstract'                   => true, 'access'                     => true,
        'code'                       => true,
        'deprec'                     => true,
        'endcode'                    => true, 'exception'                  => true,
        'final'                      => true,
        'ingroup'                    => true, 'inheritdoc'                 => true, 'inheritDoc'                 => true,
        'magic'                      => true,
        'name'                       => true,
        'toc'                        => true, 'tutorial'                   => true,
        'private'                    => true,
        'static'                     => true, 'staticvar'                  => true, 'staticVar'                  => true,
        'throw'                      => true,
        // PHPDocumentor 2 tags.
        'api'                        => true, 'author'                     => true,
        'category'                   => true, 'copyright'                  => true,
        'deprecated'                 => true,
        'example'                    => true,
        'filesource'                 => true,
        'global'                     => true,
        'ignore'                     => true, /* Can we enable this? 'index' => true, */ 'internal'                   => true,
        'license'                    => true, 'link'                       => true,
        'method'                     => true,
        'package'                    => true, 'param'                      => true, 'property'                   => true, 'property-read'              => true, 'property-write'             => true,
        'return'                     => true,
        'see'                        => true, 'since'                      => true, 'source'                     => true, 'subpackage'                 => true,
        'throws'                     => true, 'todo'                       => true, 'TODO'                       => true,
        'usedby'                     => true, 'uses'                       => true,
        'var'                        => true, 'version'                    => true,
        // PHPUnit tags
        'codeCoverageIgnore'         => true, 'codeCoverageIgnoreStart'    => true, 'codeCoverageIgnoreEnd'      => true,
        // PHPCheckStyle
        'SuppressWarnings'           => true,
        // PHPStorm
        'noinspection'               => true,
        // PEAR
        'package_version'            => true,
        // PlantUML
        'startuml'                   => true, 'enduml'                     => true,
        // Symfony 3.3 Cache Adapter
        'experimental'               => true,
        // Slevomat Coding Standard
        'phpcsSuppress'              => true,
        // PHP CodeSniffer
        'codingStandardsIgnoreStart' => true,
        'codingStandardsIgnoreEnd'   => true,
    ];
    protected $targetMap               = [
        'ALL'        => 'FUNCTION,TYPE,FIELD,METHOD',
        'CLASS'      => 'TYPE',
        'METHOD'     => 'METHOD',
        'PROPERTY'   => 'FIELD',
        'ANNOTATION' => 'TYPE',
    ];


    /**
     * process
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return void
     */
    public function process(InputInterface $input, OutputInterface $output): void
    {
        $docParser   = new DocParser();
        $docParser->setImports(static::$globalImports);
        $docParser->setIgnoredAnnotationNamespaces(static::$globalIgnoredNames);
        $composerDir = dirname(LOEYE_DIR) . '/vendor';
        $messages    = [];
        foreach ($this->mapping as $key => $item) {
            $dir = realpath($composerDir . D_S . $item['root'] . D_S . $item['namespace']) ?: realpath($composerDir . D_S . $item['root']);
            foreach (new IteratorIterator(new FilesystemIterator($dir)) as $path => $fileInfo) {
                if ($fileInfo->isFile()) {
                    $message = $this->parse($docParser, $input, $output, $fileInfo, $item);
                    if ($message) {
                        $messages[] = $message;
                    }
                }
            }
        }
        $this->output($input, $output, $messages);
        $output->writeln('done');
    }


    /**
     * output
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @param array           $messages
     */
    protected function output(InputInterface $input, OutputInterface $output, $messages): void
    {
        $file       = $input->getArgument('file');
        $fileSystem = new Filesystem();
        $content    = implode("\r\n", $messages);
        $fileSystem->dumpFile($file, $content);
    }


    /**
     * parse
     *
     * @param DocParser $docParser
     * @param InputInterface $input
     * @param OutputInterface $output
     * @param SplFileInfo $fileInfo
     * @param array $item
     * @return string|null
     */
    protected function parse(DocParser $docParser, InputInterface $input, OutputInterface $output, SplFileInfo $fileInfo, $item): ?string
    {
        $className = '\\' . $item['namespace'] . '\\' . $fileInfo->getBaseName('.php');
        $annotationName = $input->getOption('short') ? $item['short'] .'\\\\'.$fileInfo->getBaseName('.php') : str_replace('\\', '\\\\', ltrim($className, '\\'));
        try {
            $refClass = new ReflectionClass($className);
            if (!$refClass->isInterface() && !$refClass->isAbstract()) {
                $annotations = $docParser->parse($refClass->getDocComment());
                if ($annotations) {
                    foreach ($annotations as $annotation) {
                        if ($annotation instanceof Target) {
                            $targets        = $this->parseTarget($annotation->value);
                            $properties     = $refClass->getProperties();
                            return $this->build($annotationName, $targets, $this->parseProperties($properties));
                        }
                    }
                }
            }
        } catch (\ReflectionException $e) {
            $output->writeln($e->getMessage());
            $output->write($e->getTraceAsString());
        }
        return null;
    }


    /**
     * parseTarget
     *
     * @param array $targets
     *
     * @return string
     */
    protected function parseTarget($targets): string
    {
        $targets = array_map(function($item) {
            return $this->targetMap[$item];
        }, $targets);
        $targets = array_unique($targets);
        return implode(',', $targets);
    }


    /**
     * parseProperties
     *
     * @param array $properties
     *
     * @return string
     */
    protected function parseProperties($properties): string
    {
        $initial = '';
        $content = array_reduce($properties, static function($carry, \ReflectionProperty $property) {
            if ($property->isPublic()) {
                return $carry . ', ' . $property->getName() . '="${' . $property->getName() . '}"';
            }
            return $carry;
        }, $initial);
        return ltrim($content, ', ');
    }


    /**
     * build
     *
     * @param string $annotation
     * @param string $types
     * @param string $params
     *
     * @return string
     */
    protected function build($annotation, $types, $params): string
    {
        $content = str_replace(['{index}', '{annotation}', '{types}', '{params}'], [$this->index, $annotation, $types, $params], $this->template);
        $this->index++;
        return $content;
    }

}
