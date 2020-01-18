<?php

namespace loeye\unit\base;

/**
 * Generated by PHPUnit_SkeletonGenerator on 2020-01-18 at 13:55:22.
 */
class ConfigurationTest extends \loeye\unit\TestCase {

    /**
     * @var Configuration
     */
    protected $object;


    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $baseDir = PROJECT_UNIT_DIR . DIRECTORY_SEPARATOR . 'config';
        $cacheDir = PROJECT_UNIT_RUNTIME_DIR . DIRECTORY_SEPARATOR;
        $definition = new \loeye\config\app\ConfigDefinition();
        $this->object = new \loeye\base\Configuration('unit', 'app', $definition, null, $baseDir, $cacheDir);
    }


    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {
        
    }


    /**
     * @covers loeye\base\Configuration::getBaseDir
     * @todo   Implement testGetBaseDir().
     */
    public function testGetBaseDir()
    {
        $expected = PROJECT_UNIT_DIR .DIRECTORY_SEPARATOR. 'config'.DIRECTORY_SEPARATOR.'unit';
        $actual = $this->object->getBaseDir();
        $this->assertEquals($expected, $actual);
    }


    /**
     * @covers loeye\base\Configuration::getBundle
     * @todo   Implement testGetBundle().
     */
    public function testGetBundle()
    {
        $expected = 'app';
        $actual = $this->object->getBundle();
        $this->assertEquals($expected, $actual);
    }


    /**
     * @covers loeye\base\Configuration::getContext
     * @todo   Implement testGetContext().
     */
    public function testGetContext()
    {
        $expected = null;
        $actual = $this->object->getContext();
        $this->assertEquals($expected, $actual);
    }


    /**
     * @covers loeye\base\Configuration::setDefinition
     * @todo   Implement testSetDefinition().
     */
    public function testSetDefinition()
    {
        $this->assertTrue(true);
    }


    /**
     * @covers loeye\base\Configuration::getDefinition
     * @todo   Implement testGetDefinition().
     */
    public function testGetDefinition()
    {
        $this->assertIsArray($this->object->getDefinition());
    }


    /**
     * @covers loeye\base\Configuration::bundle
     * @todo   Implement testBundle().
     */
    public function testBundle()
    {
        $this->assertTrue(true);
    }


    /**
     * @covers loeye\base\Configuration::context
     * @todo   Implement testContext().
     */
    public function testContext()
    {
        $this->assertTrue(true);
    }


    /**
     * @covers loeye\base\Configuration::get
     * @todo   Implement testGet().
     */
    public function testGet()
    {
        $expected = 'http://localhost';
        $actual = $this->object->get('constants.BASE_SERVER_URL');
        $this->assertEquals($expected, $actual);
    }


    /**
     * @covers loeye\base\Configuration::getConfig
     * @todo   Implement testGetConfig().
     */
    public function testGetConfig()
    {
        $actual = $this->object->getConfig();
        $this->assertIsArray($actual);
    }


    /**
     * @covers loeye\base\Configuration::getSettings
     * @todo   Implement testGetSettings().
     */
    public function testGetSettings()
    {
        $actual = $this->object->getSettings();
        $this->assertIsArray($actual);
    }

}