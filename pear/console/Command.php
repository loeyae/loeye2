<?php

/**
 * Command.php
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

use loeye\base\AppConfig;
use Symfony\Component\Console\Command as BaseCommand;
use Symfony\Component\Console\Input;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Command
 *
 * @author   Zhang Yi <loeyae@gmail.com>
 */
abstract class Command extends BaseCommand\Command
{

    protected $name   = null;
    protected $desc   = 'command description';
    protected $help   = '';
    protected $args   = [];
    protected $params = [];

    /**
     * configure
     *
     * @return void
     */
    protected function configure(): void
    {
        $name = $this->name ?? strtolower(str_replace('\\commands\\', ':', get_class($this)));
        $this->setName($name)
                ->setDescription($this->desc)
                ->setHelp('php vender/bin/loeye ' . $name .' property')
                ->parseArgs()
                ->parseParams();
    }

    /**
     * parseArgs
     *
     * @return Command
     */
    protected function parseArgs(): Command
    {
        if ($this->args) {
            foreach ($this->args as $value) {
                $name        = $shortcut    = $mode        = $description = $default     = null;
                if (is_array($value)) {
                    $name        = $value[0];
                    $mode        = $value['required'] ?? null;
                    $mode        = $mode === true ? Input\InputArgument::REQUIRED : Input\InputArgument::OPTIONAL;
                    $description = $value['help'] ?? null;
                    $default     = $value['default'] ?? null;
                } else {
                    $name = $value;
                }
                $this->addArgument($name, $mode, $description, $default);
            }
        }
        return $this;
    }

    /**
     * parseParams
     *
     * @return Command
     */
    protected function parseParams(): Command
    {
        if ($this->params) {
            foreach ($this->params as $value) {
                $shortcut    = $mode        = $description = $default     = null;
                if (is_array($value)) {
                    $name        = $value[0];
                    $shortcut    = $value[1] ?? null;
                    $mode        = $value['required'] ?? null;
                    $mode        = $mode === null ? Input\InputOption::VALUE_NONE : ($mode !== false ?
                        Input\InputOption::VALUE_REQUIRED : Input\InputOption::VALUE_OPTIONAL);
                    $description = $value['help'] ?? null;
                    $default     = $value['default'] ?? null;
                } else {
                    $name = $value;
                }
                $this->addOption($name, $shortcut, $mode, $description, $default);
            }
        }
        return $this;
    }

    /**
     * execute
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return void
     */
    protected function execute(InputInterface $input, OutputInterface $output): void
    {
        $this->process($input, $output);
    }

    /**
     * loadAppConfig
     *
     * @param string $property
     *
     * @return AppConfig
     */
    protected function loadAppConfig($property): AppConfig
    {
        return new AppConfig($property);
    }

    /**
     * process
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @reutn mixed
     */
    abstract public function process(InputInterface $input, OutputInterface $output);
}
