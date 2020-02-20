<?php

/**
 * GenerateConfig.php
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

/**
 * GenerateConfig
 *
 * @author   Zhang Yi <loeyae@gmail.com>
 */
class GenerateConfig extends Command {

    protected $name   = 'loeye:generate-config';
    protected $args   = [
        ['property', 'required' => true, 'help' => 'property name', 'default' => null],
        ['type', 'required' => true, 'help' => 'configuration type: app-master,app-delta,cache,database,module,router,valid-rule,valid-delta,ruleset,delta', 'default' => null],
    ];
    protected $params = [
        ['file', 'f', 'required' => false, 'help' => 'file name', 'default' => 'master']
    ];
    static protected $configDefinition = [
        'app-master'  => \loeye\config\app\ConfigDefinition::class,
        'app-delta'   => \loeye\config\app\DeltaDefinition::class,
        'cache'       => \loeye\config\cache\ConfigDefinition::class,
        'database'    => \loeye\config\database\ConfigDefinition::class,
        'module'      => \loeye\config\module\ConfigDefinition::class,
        'router'      => \loeye\config\router\ConfigDefinition::class,
        'valid-rule'  => \loeye\config\validate\RulesetConfigDefinition::class,
        'valid-delta' => \loeye\config\validate\DeltaConfigDefinition::class,
        'ruleset'     => \loeye\config\general\RulesetConfigDefinition::class,
        'delta'       => \loeye\config\general\DeltaConfigDefinition::class,
    ];


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
        $property      = $input->getArgument('property');
        $type          = $input->getArgument('type');
        $name          = $input->getOption('file');
        $file          = $this->getConfigPath($property, $type, $name);
        $dumper        = new \Symfony\Component\Config\Definition\Dumper\YamlReferenceDumper();
        $configuration = $this->getConfiguration($type);
        $content       = $dumper->dump($configuration);
        $fileSystem    = new \Symfony\Component\Filesystem\Filesystem();
        $fileSystem->dumpFile($file, $content);
        $output->writeln(sprintf("output file: %1s", $file));
    }


    /**
     * getConfiguration
     * 
     * @param string $type
     * 
     * @return string
     */
    protected function getConfiguration($type)
    {
        if (array_key_exists($type, static::$configDefinition)) {
            $reflection = new \ReflectionClass(static::$configDefinition[$type]);
            return $reflection->newInstanceArgs();
        }
        return null;
    }


    /**
     * getConfigPath
     * 
     * @param string $property
     * @param string $type
     * @param string $file
     * @return string
     */
    protected function getConfigPath($property, $type, $file = 'master')
    {
        switch ($type) {
            case 'app-master':
            case 'app-delta':
                return PROJECT_CONFIG_DIR . D_S . $property . D_S . 'app' . D_S . $file . '.yml';
            case 'cache':
            case 'database':
                return PROJECT_CONFIG_DIR . D_S . $property . D_S . $type . D_S . $file . '.yml';
            case 'module':
                return PROJECT_CONFIG_DIR . D_S . 'modules' . D_S . $property . D_S . $file . '.yml';
            case 'router':
                return PROJECT_CONFIG_DIR . D_S . $type . D_S . $property . D_S . $file . '.yml';
            case 'valid-rule':
            case 'valid-delta':
                return PROJECT_CONFIG_DIR . D_S . 'validate' . D_S . $property . D_S . $file . '.yml';
            default:
                return PROJECT_CONFIG_DIR . D_S . $property . D_S . 'general' . D_S . $file . '.yml';
                break;
        }
    }

}
