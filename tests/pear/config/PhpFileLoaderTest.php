<?php

namespace loeye\unit\config;

use loeye\config\FileLocator;
use loeye\config\PhpFileLoader;
use loeye\unit\TestCase;

/**
 * Generated by PHPUnit_SkeletonGenerator on 2020-01-08 at 13:46:16.
 */
class PhpFileLoaderTest extends TestCase {

    /**
     * @var PhpFileLoader
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp() {
        $paths = PROJECT_CONFIG_DIR;
        $locator = new FileLocator($paths);
        $this->object = new PhpFileLoader($locator);
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown() {

    }

    /**
     * @covers PhpFileLoader::load
     * @todo   Implement testLoad().
     */
    public function testLoad() {
        $resource = $this->object->load('unit/app/master.php');
        $this->assertCount(1, $resource);
        $this->assertArrayHasKey('settings', $resource[0]);
    }

    /**
     * @covers PhpFileLoader::supports
     * @todo   Implement testSupports().
     */
    public function testSupports() {
        $this->assertTrue($this->object->supports('master.php'));
        $this->assertFalse($this->object->supports('master.yaml'));
    }

}
