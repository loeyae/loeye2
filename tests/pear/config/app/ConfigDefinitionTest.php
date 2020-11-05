<?php

namespace loeye\unit\config\app;

use loeye\config\app\ConfigDefinition;
use loeye\config\Processor;
use loeye\unit\TestCase;
use Symfony\Component\Config\Definition\Dumper\YamlReferenceDumper;
use Symfony\Component\Yaml\Parser;

/**
 * Generated by PHPUnit_SkeletonGenerator on 2020-01-08 at 14:21:14.
 */
class ConfigDefinitionTest extends TestCase {

    /**
     * @var ConfigDefinition
     */
    protected $object;


    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->object = new ConfigDefinition;
    }


    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {

    }

    /**
     * @covers \loeye\config\app\ConfigDefinition::getConfigTreeBuilder
     */
    public function testGetConfigTreeBuilder()
    {
        $dumper = new YamlReferenceDumper();
        $definition = $dumper->dump($this->object);
        $this->assertStringContainsString('settings', $definition);
        $this->assertStringContainsString('constants', $definition);
        $this->assertStringContainsString('application', $definition);
        $this->assertStringContainsString('configuration', $definition);
        $this->assertStringContainsString('0', $dumper->dumpAtPath($this->object, 'settings'));
        $processor = new Processor();
        $parser = new Parser();
        $configs = $parser->parseFile(PROJECT_CONFIG_DIR.DIRECTORY_SEPARATOR.'unit/app/master.yml');
        $settings = $processor->processConfiguration($this->object, $configs);
        $this->assertIsArray($settings);
        $this->assertArrayHasKey('settings', $settings);
    }

    /**
     * @covers \loeye\config\app\ConfigDefinition::getConfigTreeBuilder
     */
    public function testGetConfigTreeBuilderTest()
    {
        $processor = new Processor();
        $parser = new Parser();
        $configs = $parser->parseFile(PROJECT_CONFIG_DIR.DIRECTORY_SEPARATOR.'unit/app/test.yml');
        $settings = $processor->processConfiguration($this->object, $configs);
        $this->assertIsArray($settings);
        $this->assertArrayHasKey('settings', $settings);
    }

    /**
     * @covers \loeye\config\app\ConfigDefinition::getConfigTreeBuilder
     */
    public function testGetConfigTreeBuilderMulti()
    {
        $processor = new Processor();
        $parser = new Parser();
        $configs = $parser->parseFile(PROJECT_CONFIG_DIR.DIRECTORY_SEPARATOR.'unit/app/multi.yml');
        $settings = $processor->processConfiguration($this->object, $configs);
        $this->assertIsArray($settings);
        $this->assertArrayHasKey('settings', $settings);
    }

}
