<?php

/**
 * GetKeyDB.php
 *
 * Licensed under the Apache License, Version 2.0 (the "License"),
 * see LICENSE for more details: http://www.apache.org/licenses/LICENSE-2.0.
 *
 * @author  Zhang Yi <loeyae@gmail.com>
 * @version SVN: $Id: Zhang Yi $
 */
namespace loeye\commands;

use loeye\console\Command;
use loeye\lib\Secure;
use Symfony\Component\Console\{Input\InputInterface, Output\OutputInterface};

/**
 * GetKeyDB
 *
 * @author   Zhang Yi <loeyae@gmail.com>
 */
class GetKeyDB extends Command
{

    protected $name   = 'loeye:getkeydb';
    protected $desc   = 'get value from key db';
    protected $args   = [
        ['property', 'required' => true, 'help' => 'property name', 'default' => null],
        ['key', 'required' => true, 'help' => 'keydb name', 'default' => null],
        ['group', 'required' => false, 'help' => 'keydb group', 'default' => null],
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
        $value = Secure::getKeyDb($input->getArgument('property'), $input->getArgument('key'), $input->getArgument('group'));
        $output->writeln($value);
    }

}
