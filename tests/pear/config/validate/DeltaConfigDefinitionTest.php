<?php

namespace loeye\unit\config\validate;

use loeye\config\Processor;
use loeye\config\validate\DeltaConfigDefinition;
use loeye\unit\TestCase;
use Symfony\Component\Config\Definition\Dumper\YamlReferenceDumper;
use Symfony\Component\Yaml\Parser;

/**
 * Generated by PHPUnit_SkeletonGenerator on 2020-01-16 at 11:20:55.
 */
class DeltaConfigDefinitionTest extends TestCase
{

    /**
     * @var DeltaConfigDefinition
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->object = new DeltaConfigDefinition;
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {

    }

    /**
     * @cover DeltaConfigDefinition::getConfigTreeBuilder
     * @todo   Implement testGetConfigTreeBuilder().
     */
    public function testGetConfigTreeBuilder()
    {
        $dumper = new YamlReferenceDumper();
        $definition = $dumper->dump($this->object);
        $this->assertStringContainsString('settings', $definition);
        $this->assertStringContainsString('validate', $definition);
        $processor = new Processor();
        $parser = new Parser();
        $configs = $parser->parseFile(PROJECT_CONFIG_DIR.DIRECTORY_SEPARATOR.'validate/unit/delta.yml');
        $settings = $processor->processConfiguration($this->object, $configs);
        $this->assertIsArray($settings);
        $this->assertArrayHasKey('settings', $settings);
        $this->assertArrayHasKey('validate', $settings);
    }

}
