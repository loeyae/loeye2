<?php

/**
 * SetKeyDB.php
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
use loeye\lib\Secure;

/**
 * SetKeyDB
 *
 * @author   Zhang Yi <loeyae@gmail.com>
 */
class SetKeyDB extends Command
{

    protected $name   = 'loeye:setkeydb';
    protected $desc   = 'set key db value';
    protected $args   = [
        ['property', 'required' => true, 'help' => 'property name', 'default' => null],
        ['keydb', 'required' => true, 'help' => 'keydb name', 'default' => null],
        ['key', 'required' => true, 'help' => 'key name', 'default' => null],
        ['value', 'required' => true, 'help' => 'key value', 'default' => null],
        ['group', 'required' => false, 'help' => 'keydb group', 'default' => null],
        ['expiry', 'required' => false, 'help' => 'expiry time', 'default' => 0],
    ];
    protected $params;

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
        $property = $input->getArgument('property');
        $keydb = $input->getArgument('keydb');
        $key = $input->getArgument('key');
        $value = $input->getArgument('value');
        $group = $input->getArgument('group');
        $expiry = $input->getArgument('expiry');
        Secure::setKeyDb($property, $keydb, $key, $value, $group, $expiry);
        $output->writeln('done');
    }

}
