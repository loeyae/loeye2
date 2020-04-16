<?php

namespace loeye\unit\config;

use loeye\config\FileLocator;
use loeye\config\YamlFileLoader;
use loeye\unit\TestCase;

/**
 * Generated by PHPUnit_SkeletonGenerator on 2020-01-07 at 15:46:13.
 */
class YamlFileLoaderTest extends TestCase {

    /**
     * @var YamlFileLoader
     */
    protected $object;

    /**
     *
     * @var FileLocator
     */
    protected $locator;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp() {
        $paths = PROJECT_CONFIG_DIR;
        $this->locator = new FileLocator($paths);
        $this->object = new YamlFileLoader($this->locator);
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown() {

    }

    /**
     * @cover YamlFileLoader::load
     * @todo   Implement testLoad().
     */
    public function testLoad() {
        $masterYml = 'unit/app/master.yml';
        $resource = $this->object->load($masterYml);
        $this->assertCount(1, $resource);
        $this->assertArrayHasKey("settings", $resource[0]);
    }

    /**
     * @cover YamlFileLoader::import
     * @todo   Implement testLoad().
     */
    public function testImport() {
        $this->object->setCurrentDir('unit/app');
        $resource = $this->object->import('*.yml');
        $this->assertCount(4, $resource);
    }

    /**
     * @cover YamlFileLoader::supports
     * @todo   Implement testSupports().
     */
    public function testSupports() {
        $this->assertTrue($this->object->supports('master.yml'));
        $this->assertFalse($this->object->supports('master.php'));
    }

}
