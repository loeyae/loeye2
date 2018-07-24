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
 * @version  GIT: $Id: Zhang Yi $
 * @link     https://github.com/loeyae/loeye.git
 */

namespace loeye\console;

use Symfony\Component\Console\Command as BaseCommand;
use Symfony\Component\Console\Input;
use Symfony\Component\Console\Output;

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
    protected function configure()
    {
        $name = $this->name ?? strtolower(str_replace('\\commands\\', ':', get_class($this)));
        $this->setName($name)
                ->setDescription($this->desc)
                ->setHelp('php bin/cli ' . $this->name)
                ->parseArgs()
                ->parseParams();
    }

    /**
     * parseArgs
     *
     * @return void
     */
    protected function parseArgs()
    {
        if ($this->args) {
            foreach ($this->args as $value) {
                $name        = $shortcut    = $mode        = $description = $default     = null;
                if (is_array($value)) {
                    $name        = $value[0];
                    $mode        = $value['required'] ?? null;
                    $mode        = $mode == true ? Input\InputArgument::REQUIRED : Input\InputArgument::OPTIONAL;
                    $description = $value['help'] ?? null;
                    $default     = $value['default'] ?? null;
                } else {
                    $name = $value;
                }
                $this->addArgument($name        = $name, $mode        = $mode, $description = $description, $default     = $default);
            }
        }
        return $this;
    }

    /**
     * parseParams
     *
     * @return void
     */
    protected function parseParams()
    {
        if ($this->params) {
            foreach ($this->params as $value) {
                $name        = $shortcut    = $mode        = $description = $default     = null;
                if (is_array($value)) {
                    $name        = $value[0];
                    $shortcut    = $value[1] ?? null;
                    $mode        = $value['required'] ?? null;
                    $mode        = $mode === null ? Input\InputOption::VALUE_NONE : ($mode == true ? Input\InputOption::VALUE_REQUIRED : Input\InputOption::VALUE_OPTIONAL);
                    $description = $value['help'] ?? null;
                    $default     = $value['default'] ?? null;
                } else {
                    $name = $value;
                }
                $this->addOption($name        = $name, $shortcut    = $shortcut, $mode        = $mode, $description = $description, $default     = $default);
            }
        }
        return $this;
    }

    /**
     * execute
     *
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     *
     * @return void
     */
    protected function execute(Input\InputInterface $input, Output\OutputInterface $output)
    {
        $this->process($input, $output);
    }

    /**
     * process
     *
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     *
     * @reutn void
     */
    abstract public function process(Input\InputInterface $input, Output\OutputInterface $output);
}
