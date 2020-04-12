<?php

/**
 * EntityGeneratorTraite.php
 * 
 * Licensed under the Apache License, Version 2.0 (the "License"),
 * see LICENSE for more details: http://www.apache.org/licenses/LICENSE-2.0.
 *
 * @author  Zhang Yi <loeyae@gmail.com>
 * @version 2020年2月20日 下午3:24:25
 */

namespace loeye\console\helper;

use Doctrine\ORM\Tools\Console\MetadataFilter;
use Symfony\Component\Console\{Input\InputInterface, Output\OutputInterface, Style\SymfonyStyle};
use InvalidArgumentException;
use loeye\base\DB;
use loeye\base\Exception;
use RuntimeException;
use Symfony\Component\Filesystem\Filesystem;

/**
 * EntityGeneratorTrait
 *
 * @author   Zhang Yi <loeyae@gmail.com>
 */
trait EntityGeneratorTrait {


    /**
     * getNamespace
     * 
     * @param string $destDir
     * @return string
     */
    protected function getNamespace($destDir): string
    {
        $dir = substr($destDir, strlen(PROJECT_DIR) + 1);
        return PROJECT_NAMESPACE . '\\' . $dir;
    }


    /**
     * process
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return mixed
     * @throws Exception
     */
    public function process(InputInterface $input, OutputInterface $output)
    {
        $ui       = new SymfonyStyle($input, $output);
        $property = $input->getArgument('property');
        $force    = $input->getOption('force');

        $appConfig = $this->loadAppConfig($property);
        $type      = $input->getOption('db-id');
        $db        = DB::getInstance($appConfig, $type);
        $em        = $db->em();

        $metaData = $em->getMetadataFactory()->getAllMetadata();
        $metaData = MetadataFilter::filter($metaData, $input->getOption('filter'));

        $destPath = $this->getDestPath($input, $ui);

        if (!file_exists($destPath)) {
            $fileSystem = new Filesystem();
            $fileSystem->mkdir($destPath);
            $destPath   = realpath($destPath);
        }

        if (!is_writable($destPath)) {
            throw new InvalidArgumentException(
                    sprintf("Entities destination directory '<info>%s</info>' does not have write permissions.", $destPath)
            );
        }

        if (empty($metaData)) {
            $ui->success('No Metadata Classes to process.');
            return 0;
        }
        $namespace = $this->getNamespace($destPath);

        $numFiles = 0;

        foreach ($metaData as $metadata) {
            if ($metadata->reflFields) {
                $this->generateFile($ui, $metadata, $namespace, $destPath, $force);
                ++$numFiles;
            }
        }

        if ($numFiles === 0) {
            $ui->text('No file were found to be processed.');
            return 0;
        }

        // Outputting information message
        $ui->newLine();
        $ui->text(sprintf('classes generated to "<info>%s</info>"', $destPath));

        return 0;
    }


    /**
     * writeFile
     * 
     * @param string $outputDirectory
     * @param string $className
     * @param string $code
     * @param string $force
     */
    protected function writeFile($outputDirectory, $className, $code, $force): void
    {
        $path = $outputDirectory . D_S
                . str_replace('\\', D_S, $className) . '.php';
        $dir  = dirname($path);

        if (!file_exists($dir) && !mkdir($dir, 0775, true) && !is_dir($dir)) {
            throw new RuntimeException(sprintf('Directory "%s" was not created', $dir));
        }

        if ($force || !file_exists($path)) {
            file_put_contents($path, $code);
            chmod($path, 0664);
        }
    }

    /**
     * generateTemplate
     *
     * @param array $variables
     * @param string $template
     *
     * @return string
     */
    protected static function generateTemplate(array $variables, string $template): string
    {
        return str_replace(array_keys($variables), array_values($variables), $template);
    }

}
