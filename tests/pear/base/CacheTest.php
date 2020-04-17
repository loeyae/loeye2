<?php
/**
 * CacheTest.php
 *
 * PHP version 7
 *
 * Licensed under the Apache License, Version 2.0 (the "License"),
 * see LICENSE for more details: http://www.apache.org/licenses/LICENSE-2.0.
 *
 * @category PHP
 * @package  LOEYE
 * @author   Zhang Yi <loeyae@gmail.com>
 * @version  2020/4/17 14:09
 * @link     https://github.com/loeyae/loeye2.git
 */

namespace loeye\unit\base;

use loeye\base\Cache;
use loeye\base\Exception;
use loeye\unit\TestCase;
use ReflectionException;
use Symfony\Component\Cache\Adapter\ApcuAdapter;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\Cache\Adapter\MemcachedAdapter;
use Symfony\Component\Cache\Adapter\PhpArrayAdapter;
use Symfony\Component\Cache\Adapter\PhpFilesAdapter;
use Symfony\Component\Cache\Adapter\RedisAdapter;
use Symfony\Component\Cache\Exception\CacheException;
use Symfony\Component\Filesystem\Filesystem;

class CacheTest extends TestCase
{

    protected function setUp()
    {
        $_ENV['LOEYE_PROFILE_ACTIVE'] = 'dev';
        parent::setUp();
        $cacheDir = RUNTIME_CACHE_DIR .D_S.'app.unit.file';
        $fileSystem = new Filesystem();
        if(realpath($cacheDir)) {
            $fileSystem->remove($cacheDir);
        }
    }

    protected function tearDown()
    {
        parent::tearDown();
        $cacheDir = RUNTIME_CACHE_DIR .D_S.'app.unit.file';
        $fileSystem = new Filesystem();
        if(realpath($cacheDir)) {
            $fileSystem->remove($cacheDir);
        }
    }

    /**
     * @covers \loeye\base\Cache::setMulti
     * @covers \loeye\base\Cache::getMulti
     * @covers \loeye\base\Cache::delete
     * @covers \loeye\base\Cache::_buildInstance
     */
    public function testSetMulti()
    {
        $cache = Cache::getInstance($this->appConfig);
        $time = time();
        $cache->setMulti(['unit' => 'test', 'namespace' => 'unit', 'time' => $time]);
        $cacheItems = $cache->getMulti(['unit', 'namespace', 'time', 'test']);
        $values = [];
        foreach ($cacheItems as $item) {
            $values[$item->getKey()] = $item->get();
        }
        $this->assertArrayHasKey('unit', $values);
        $this->assertEquals('test', $values['unit']);
        $this->assertArrayHasKey('namespace', $values);
        $this->assertEquals('unit',$values['namespace']);
        $this->assertArrayHasKey('time', $values);
        $this->assertEquals($time, $values['time']);
        $this->assertArrayHasKey('test', $values);
        $this->assertNull($values['test']);
        $ret = $cache->delete('namespace');
        $this->assertTrue($ret);
        $this->assertFalse($cache->has('namespace'));
    }

    /**
     * @covers \loeye\base\Cache::set
     * @covers \loeye\base\Cache::get
     * @covers \loeye\base\Cache::has
     */
    public function testSet()
    {
        $cache = Cache::getInstance($this->appConfig);
        $time = time();
        $cache->set('time', $time);
        $this->assertTrue($cache->has('time'));
        $this->assertEquals($time, $cache->get('time'));
        $this->assertFalse($cache->has('test'));
    }

    /**
     * @covers \loeye\base\Cache::append
     * @covers \loeye\base\Cache::remove
     * @covers \loeye\base\Cache::set
     * @covers \loeye\base\Cache::get
     *
     * @throws CacheException
     * @throws Exception
     * @throws \Psr\Cache\InvalidArgumentException
     */
    public function testAppend()
    {
        $cache = Cache::getInstance($this->appConfig);
        $cache->set('sample', ['now']);
        $this->assertContains('now', $cache->get('sample'));
        $this->assertNotContains('time', $cache->get('sample'));
        $time = time();
        $cache->append('sample', ['time' => $time]);
        $this->assertArrayHasKey('time', $cache->get('sample'));
        $this->assertContains('now', $cache->get('sample'));
        $cache->remove('sample', 'now');
        $this->assertArrayHasKey('time', $cache->get('sample'));
        $this->assertNotContains('now', $cache->get('sample'));
        $cache->remove('sample', 'time');
        $this->assertFalse($cache->has('sample'));
    }

    /**
     * @covers \loeye\base\Cache::getInstance
     * @covers \loeye\base\Cache::init
     * @covers \loeye\base\Cache::_buildInstance
     * @covers \loeye\base\Cache::__construct
     *
     * @throws CacheException
     * @throws Exception
     * @throws ReflectionException
     */
    public function testGetInstance()
    {
        $cache = Cache::getInstance($this->appConfig);
        $this->assertInstanceOf(FilesystemAdapter::class, $this->getAdapter($cache));
        unset($cache);
        $cache = Cache::getInstance($this->appConfig, Cache::CACHE_TYPE_APC);
        $this->assertInstanceOf(ApcuAdapter::class, $this->getAdapter($cache));
        unset($cache);
        $cache = Cache::getInstance($this->appConfig, Cache::CACHE_TYPE_PHP_ARRAY);
        $this->assertInstanceOf(PhpArrayAdapter::class, $this->getAdapter($cache));
        unset($cache);
        $cache = Cache::getInstance($this->appConfig, Cache::CACHE_TYPE_PHP_FILE);
        $this->assertInstanceOf(PhpFilesAdapter::class, $this->getAdapter($cache));
        unset($cache);
        $cache = Cache::init($this->appConfig, Cache::CACHE_TYPE_ARRAY);
        $this->assertInstanceOf(ArrayAdapter::class, $this->getAdapter($cache));
        unset($cache);
        $stub = $this->createMock(Cache::class);
        if (class_exists('\\Memcached')) {
            $stub->expects($this->any())->method('getMemcachedClient')->willReturn(new \Memcached());
            $cache = Cache::getInstance($this->appConfig, Cache::CACHE_TYPE_MEMCACHED);
            $this->assertInstanceOf(MemcachedAdapter::class, $this->getAdapter($cache));
            unset($cache);
        }
        if (class_exists('\\Redis')) {
            $stub->expects($this->any())->method('getRedisClient')->willReturn(new \Redis());
            $cache = new $stub($this->appConfig, Cache::CACHE_TYPE_REDIS);
            $this->assertInstanceOf(RedisAdapter::class, $this->getAdapter($cache));
            unset($cache);
        }
    }

    protected function getAdapter(Cache $cache)
    {
        $reflection = new \ReflectionClass(Cache::class);
        $property = $reflection->getProperty('instance');
        $property->setAccessible(true);
        return $property->getValue($cache);
    }

    public function test__call()
    {
        $cache = Cache::getInstance($this->appConfig);
        $ret = $cache->commit();
        $this->assertTrue($ret);
        $ret = $cache->test();
        $this->assertNull($ret);
    }

}
