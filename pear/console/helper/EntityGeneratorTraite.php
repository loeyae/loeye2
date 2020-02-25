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

use \Symfony\Component\Console\{
    Input\InputInterface,
    Output\OutputInterface
};

/**
 * EntityGeneratorTraite
 *
 * @author   Zhang Yi <loeyae@gmail.com>
 */
trait EntityGeneratorTraite {


    /**
     * getNamespace
     * 
     * @param string $destDir
     * @return string
     */
    protected function getNamespace($destDir)
    {
        $dir = substr($destDir, strlen(PROJECT_DIR) + 1);
        return PROJECT_NAMESPACE . '\\' . $dir;
    }


    /**
     * process
     *
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     *
     * @return void
     */
    public function process(InputInterface $input, OutputInterface $output)
    {
        $ui       = new \Symfony\Component\Console\Style\SymfonyStyle($input, $output);
        $property = $input->getArgument('property');
        $force    = $input->getOption('force');

        $appConfig = $this->loadAppConfig($property);
        $type      = $input->getOption('db-id');
        $db        = \loeye\base\DB::getInstance($appConfig, $type);
        $em        = $db->em();

        $metadatas = $em->getMetadataFactory()->getAllMetadata();
        $metadatas = \Doctrine\ORM\Tools\Console\MetadataFilter::filter($metadatas, $input->getOption('filter'));

        $destPath = $this->getDestPath($input);

        if (!file_exists($destPath)) {
            $fileSystem = new \Symfony\Component\Filesystem\Filesystem();
            $fileSystem->mkdir($destPath);
            $destPath   = realpath($destPath);
        }

        if (!is_writable($destPath)) {
            throw new \InvalidArgumentException(
                    sprintf("Entities destination directory '<info>%s</info>' does not have write permissions.", $destPath)
            );
        }

        if (empty($metadatas)) {
            $ui->success('No Metadata Classes to process.');
            return 0;
        }
        $namespace = $this->getNamespace($destPath);

        $numFiles = 0;

        foreach ($metadatas as $metadata) {
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
    protected function writeFile($outputDirectory, $className, $code, $force)
    {
        $path = $outputDirectory . \DIRECTORY_SEPARATOR
                . str_replace('\\', \DIRECTORY_SEPARATOR, $className) . '.php';
        $dir  = dirname($path);

        if (!is_dir($dir)) {
            mkdir($dir, 0775, true);
        }

        if ($force || !file_exists($path)) {
            file_put_contents($path, $code);
            chmod($path, 0664);
        }
    }

}
