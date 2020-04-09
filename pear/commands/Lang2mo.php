<?php

/**
 * Lang2mo.php
 *
 * Licensed under the Apache License, Version 2.0 (the "License"),
 * see LICENSE for more details: http://www.apache.org/licenses/LICENSE-2.0.
 *
 * @author  Zhang Yi <loeyae@gmail.com>
 * @version SVN: $Id: Zhang Yi $
 */

namespace loeye\commands;

use loeye\console\Command;
use \Symfony\Component\Console\{
    Input\InputInterface,
    Output\OutputInterface
};
use RuntimeException;
use SplFileObject;

/**
 * Lang2mo
 *
 * @author   Zhang Yi <loeyae@gmail.com>
 */
class Lang2mo extends Command
{

    protected $name   = 'loeye:lang2mo';
    protected $desc   = 'generate .mo file from language setting';
    protected $args   = [
        ['property', 'required' => true, 'help' => 'property name', 'default' => null]
    ];
    protected $params = [];

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
        $PROPERTY = $input->getArgument('property');
        $appConfig = $this->loadAppConfig($PROPERTY);
        $EXT  = 'llt';
        $CHDIR = 'LC_MESSAGES';

        if (!function_exists('gettext')) {
            exit('gettext is not exists');
        }

        $YEAR = date('Y');
        $DATE = date('Y-m-d H:iO');

        $langDir  = PROJECT_LOCALE_DIR . DIRECTORY_SEPARATOR . $PROPERTY . DIRECTORY_SEPARATOR;
        $cacheDir = RUNTIME_CACHE_DIR . DIRECTORY_SEPARATOR . $EXT;
        if (!file_exists($cacheDir) && !mkdir($cacheDir, 0777) && !is_dir($cacheDir)) {
            throw new RuntimeException(sprintf('Directory "%s" was not created', $cacheDir));
        }

        $localSetting = $appConfig->getSetting('local');
        $domain       = $localSetting['basename'];
        $langList     = $localSetting['supported_languages'];
        $baseFile     = $langDir . $domain . '.' . $EXT;
        $baseSetting  = parse_ini_file($baseFile);
        foreach ($langList as $lang) {
            $langFile = $langDir . $domain . '_' . $lang . '.' . $EXT;
            if (is_file($langFile)) {
                $langSetting = parse_ini_file($langFile);
            } else {
                $langSetting = $baseSetting;
            }
            $langCacheDir = $cacheDir . DIRECTORY_SEPARATOR . $lang;
            if (!file_exists($langCacheDir) && !mkdir($langCacheDir, 0777) && !is_dir($langCacheDir)) {
                throw new RuntimeException(sprintf('Directory "%s" was not created', $langCacheDir));
            }
            $langCacheDir .= DIRECTORY_SEPARATOR . $CHDIR;
            if (!file_exists($langCacheDir) && !mkdir($langCacheDir, 0777) && !is_dir($langCacheDir)) {
                throw new RuntimeException(sprintf('Directory "%s" was not created', $langCacheDir));
            }
            $poFile   = $langCacheDir . DIRECTORY_SEPARATOR . $domain . '.po';
            $POHEADER = <<<EOF
# LOEYE PROJECT TRANSLATION FILE.
# Copyright (C) {$YEAR} THE PACKAGE'S COPYRIGHT HOLDER
# This file is distributed under the same license as the {$PROPERTY} package.
# Zhang Yi <loeyae@gmail.com>, {$YEAR}.
#
#, fuzzy
msgid ""
msgstr ""
"Project-Id-Version: {$PROPERTY} 0.0.1 \\n"
"Report-Msgid-Bugs-To: \\n"
"POT-Creation-Date: {$DATE}\\n"
"PO-Revision-Date: YEAR-MO-DA HO:MI+ZONE\\n"
"Last-Translator: FULL NAME <EMAIL@ADDRESS>\\n"
"Language-Team: {$PROPERTY} team\\n"
"Language: {$lang}\\n"
"MIME-Version: 1.0\\n"
"Content-Type: text/plain; charset=UTF-8\\n"
"Content-Transfer-Encoding: 8bit\\n"
\n
\n
EOF;
            file_put_contents($poFile, $POHEADER);
            $splFile  = new SplFileObject($poFile, 'a');
            $splFile->fwrite('#: ' . $PROPERTY . ' ' . $lang . PHP_EOL);
            foreach ($baseSetting as $key => $string) {
                $splFile->fwrite('msgid "' . addslashes($key) . '"' . PHP_EOL);
                if (isset($langSetting[$key])) {
                    $splFile->fwrite('msgstr "' . addslashes($langSetting[$key]) . '"' . PHP_EOL);
                } else {
                    $splFile->fwrite('msgstr "' . addslashes($string) . '"' . PHP_EOL);
                }
                $splFile->fwrite(PHP_EOL);
            }

            $descriptors = array(
                0 => array('pipe', 'r'),
                1 => array('pipe', 'w'),
                2 => array('pipe', 'w')
            );
            $moFile         = $langCacheDir . DIRECTORY_SEPARATOR . $domain . '.mo';
            $cmd            = 'msgfmt -o ' . $moFile . ' ' . $poFile;
            $process        = proc_open($cmd, $descriptors, $pipes, null, null);
            if (is_resource($process)) {
                fclose($pipes[0]);
                $outputs = stream_get_contents($pipes[1]);

                $err = stream_get_contents($pipes[2]);
                if (!empty($err)) {
                    $output->writeln("failed to execute cmd: \"$cmd\". stderr: `$err'");
                    exit;
                }

                fclose($pipes[2]);
                fclose($pipes[1]);
                proc_close($process);
                //    file_put_contents($moFile, $outputs);
            } else {
                $output->writeln("failed to execute cmd \"$cmd\"");
            }
        }
        $output->writeln('done');
    }
}
