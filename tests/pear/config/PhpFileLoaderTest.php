<?php

namespace loeye\unit\config;

/**
 * Generated by PHPUnit_SkeletonGenerator on 2020-01-08 at 13:46:16.
 */
class PhpFileLoaderTest extends \loeye\unit\TestCase {

    /**
     * @var PhpFileLoader
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp() {
        $paths = PROJECT_UNIT_DIR.DIRECTORY_SEPARATOR.'config';
        $locator = new \loeye\config\FileLocator($paths);
        $this->object = new \loeye\config\PhpFileLoader($locator);
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown() {
        
    }

    /**
     * @covers loeye\config\PhpFileLoader::load
     * @todo   Implement testLoad().
     */
    public function testLoad() {
        $resource = $this->object->load('unit/app/master.php');
        $this->assertCount(1, $resource);
        $this->assertArrayHasKey("settings", $resource[0]);
    }

    /**
     * @covers loeye\config\PhpFileLoader::supports
     * @todo   Implement testSupports().
     */
    public function testSupports() {
        $this->assertTrue($this->object->supports('master.php'));
        $this->assertFalse($this->object->supports('master.yaml'));
    }

}