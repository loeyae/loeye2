<?php

namespace loeye\unit\config;

/**
 * Generated by PHPUnit_SkeletonGenerator on 2020-01-18 at 01:33:12.
 */
class ConfigCacheTest extends \loeye\unit\TestCase {

    /**
     * @var ConfigCache
     */
    protected $object;
    
    protected $cacheDir;
    
    protected $namespace;
    
    protected $path;


    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->path = PROJECT_UNIT_DIR . DIRECTORY_SEPARATOR . 'config';
        $this->cacheDir = PROJECT_UNIT_RUNTIME_DIR . DIRECTORY_SEPARATOR .'unit';
        $fileSystem = new \Symfony\Component\Filesystem\Filesystem();
        if ($fileSystem->exists($this->cacheDir)){
            $fileSystem->remove($this->cacheDir);
        }
        
        $this->namespace = 'unit.app';
        $this->object = new \loeye\config\ConfigCache($this->path, $this->cacheDir, $this->namespace);
    }


    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {
        $fileSystem = new \Symfony\Component\Filesystem\Filesystem();
        if ($fileSystem->exists($this->cacheDir)){
            $fileSystem->remove($this->cacheDir);
        }
    }


    /**
     * @covers loeye\config\ConfigCache::nsToPattern
     * @todo   Implement testNsToPattern().
     */
    public function testNsToPattern()
    {
        $this->assertEquals('unit/app', $this->object->nsToPattern());
    }


    /**
     * @covers loeye\config\ConfigCache::getMetaFile
     * @todo   Implement testGetMetaFile().
     */
    public function testGetMetaFile()
    {
        $actual = $this->object->getMetaFile();
        $expected = PROJECT_UNIT_RUNTIME_DIR .DIRECTORY_SEPARATOR .'unit'. DIRECTORY_SEPARATOR.$this->namespace.DIRECTORY_SEPARATOR. md5(serialize([$this->path, 'unit/app'])).'.meta';
        $this->assertEquals($expected, $actual);
    }


    /**
     * @covers loeye\config\ConfigCache::getResourceByMetaFile
     * @todo   Implement testGetResourceByMetaFile().
     */
    public function testGetResourceByMetaFile()
    {
        $this->assertInstanceOf(\loeye\config\ConfigResource::class, $this->object->getResourceByMetaFile());
    }


    /**
     * @covers loeye\config\ConfigCache::isFresh
     * @todo   Implement testIsFresh().
     */
    public function testIsFresh()
    {
        $this->assertTrue($this->object->isFresh());
    }


    /**
     * @covers loeye\config\ConfigCache::cacheAdapter
     * @todo   Implement testCacheAdapter().
     */
    public function testCacheAdapter()
    {
        $this->assertInstanceOf(\Symfony\Component\Cache\Adapter\PhpFilesAdapter::class, $this->object->cacheAdapter());
    }

    
    /**
     * @covers loeye\config\ConfigCache::write
     * @todo   Implement testWrite().
     */
    public function testWriteList()
    {
        $locator = new \loeye\config\FileLocator(PROJECT_UNIT_DIR . DIRECTORY_SEPARATOR . 'config');
        $ymlLoader = new \loeye\config\YamlFileLoader($locator);
        $ymlLoader->setCurrentDir('unit/app');
        $configes = $ymlLoader->import('*.yml');
        $contents = ['master' => ['aaa'], 'delta' => ['test' => ['vvv']]];
        $this->object->write($contents);
        $actual = $this->object->cacheAdapter()->getItem("master")->get();
        $this->assertEquals($contents['master'], $actual);
    }

}
