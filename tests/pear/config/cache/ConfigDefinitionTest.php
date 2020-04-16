<?php

namespace loeye\unit\config\cache;

use loeye\config\cache\ConfigDefinition;
use loeye\config\Processor;
use loeye\unit\TestCase;
use Symfony\Component\Config\Definition\Dumper\YamlReferenceDumper;
use Symfony\Component\Yaml\Parser;

/**
 * Generated by PHPUnit_SkeletonGenerator on 2020-01-15 at 09:28:43.
 */
class ConfigDefinitionTest extends TestCase
{

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
     * @covers ConfigDefinition::getConfigTreeBuilder
     * @todo   Implement testGetConfigTreeBuilder().
     */
    public function testGetConfigTreeBuilderBase()
    {
        $dumper = new YamlReferenceDumper();
        $definition = $dumper->dump($this->object);
        $this->assertStringContainsString('settings', $definition);
        $this->assertStringContainsString('apc', $definition);
        $this->assertStringContainsString('memcached', $definition);
//        $this->assertStringContainsString("file", $definition);
        $this->assertStringContainsString('0', $dumper->dumpAtPath($this->object, "settings"));
    }

    /**
     * @covers ConfigDefinition::getConfigTreeBuilder
     * @todo   Implement testGetConfigTreeBuilder().
     */
    public function testGetConfigTreeBuilderApc()
    {
        $processor = new Processor();
        $parser = new Parser();
        $configs = $parser->parseFile(PROJECT_CONFIG_DIR.DIRECTORY_SEPARATOR.'unit/cache/apc.yml');
        $settings = $processor->processConfiguration($this->object, $configs);
        $this->assertIsArray($settings);
        $this->assertArrayHasKey('settings', $settings);
        $this->assertArrayHasKey('apc', $settings);
    }

    /**
     * @covers ConfigDefinition::getConfigTreeBuilder
     * @todo   Implement testGetConfigTreeBuilder().
     */
    public function testGetConfigTreeBuilderMecached()
    {
        $processor = new Processor();
        $parser = new Parser();
        $configs = $parser->parseFile(PROJECT_CONFIG_DIR.DIRECTORY_SEPARATOR.'unit/cache/mem.yml');
        $settings = $processor->processConfiguration($this->object, $configs);
        $this->assertIsArray($settings);
        $this->assertArrayHasKey('settings', $settings);
        $this->assertArrayHasKey('memcached', $settings);
    }

    /**
     * @covers ConfigDefinition::getConfigTreeBuilder
     * @todo   Implement testGetConfigTreeBuilder().
     */
    public function testGetConfigTreeBuilderRedis()
    {
        $processor = new Processor();
        $parser = new Parser();
        $configs = $parser->parseFile(PROJECT_CONFIG_DIR.DIRECTORY_SEPARATOR.'unit/cache/redis.yml');
        $settings = $processor->processConfiguration($this->object, $configs);
        $this->assertIsArray($settings);
        $this->assertArrayHasKey('settings', $settings);
        $this->assertArrayHasKey('redis', $settings);
    }

    /**
     * @covers ConfigDefinition::getConfigTreeBuilder
     * @todo   Implement testGetConfigTreeBuilder().
     */
    public function testGetConfigTreeBuilderPfile()
    {
        $processor = new Processor();
        $parser = new Parser();
        $configs = $parser->parseFile(PROJECT_CONFIG_DIR.DIRECTORY_SEPARATOR.'unit/cache/pfile.yml');
        $settings = $processor->processConfiguration($this->object, $configs);
        $this->assertIsArray($settings);
        $this->assertArrayHasKey('settings', $settings);
        $this->assertArrayHasKey('pfile', $settings);
    }

    /**
     * @covers ConfigDefinition::getConfigTreeBuilder
     * @todo   Implement testGetConfigTreeBuilder().
     */
    public function testGetConfigTreeBuilderFile()
    {
        $processor = new Processor();
        $parser = new Parser();
        $configs = $parser->parseFile(PROJECT_CONFIG_DIR.DIRECTORY_SEPARATOR.'unit/cache/file.yml');
        $settings = $processor->processConfiguration($this->object, $configs);
        $this->assertIsArray($settings);
        $this->assertArrayHasKey('settings', $settings);
        $this->assertArrayHasKey('file', $settings);
    }

}
