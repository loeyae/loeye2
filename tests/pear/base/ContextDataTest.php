<?php

namespace loeye\unit\base;

use loeye\base\ContextData;
use loeye\unit\TestCase;

/**
 * Generated by PHPUnit_SkeletonGenerator on 2020-01-27 at 10:02:15.
 */
class ContextDataTest extends TestCase {


    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        
    }


    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {
        
    }


    /**
     * @covers \loeye\base\ContextData::init
     * @todo   Implement testInit().
     */
    public function testInit()
    {
        $data = ContextData::init(['a' => 1]);
        $data1 = new ContextData(['a' => 1]);
        $this->assertArrayHasKey('a', $data->getData());
        $this->assertEquals($data1->getData(), $data->getData());
    }


    /**
     * @covers \loeye\base\ContextData::__toString
     * @todo   Implement test__toString().
     */
    public function test__toString()
    {
        $data = ContextData::init(['a' => 1]);
        $actual = strval($data);
        $this->assertIsString($actual);
        $this->assertStringContainsString('array', $actual);
    }


    /**
     * @covers \loeye\base\ContextData::__invoke
     * @todo   Implement test__invoke().
     */
    public function test__invoke()
    {
        $data = ContextData::init(['a' => 1]);
        $expected = $data->getData();
        $actual = $data();
        $this->assertIsArray($actual);
        $this->assertEquals($expected, $actual);
    }
    
    
    /**
     * @covers \loeye\base\ContextData::getData
     * @todo   Implement testGetData().
     */
    public function testGetData()
    {
        $actual = ['a' => 1];
        $data = ContextData::init($actual);
        $this->assertIsArray($data->getData());
        $this->assertEquals($data->getData(), $actual);
    }


    /**
     * @covers \loeye\base\ContextData::isEmpty
     * @todo   Implement testIsEmpyt().
     */
    public function testIsEmpty()
    {
        $actual1 = null;
        $data1 = ContextData::init($actual1);
        $this->assertTrue($data1->isEmpty());
        $actual2 = 0;
        $data2 = ContextData::init($actual2);
        $this->assertFalse($data2->isEmpty());
    }


    /**
     * @covers \loeye\base\ContextData::isExpire
     * @todo   Implement testIsExpire().
     */
    public function testIsExpire()
    {
        $actual = 1;
        $data = ContextData::init($actual);
        $this->assertFalse($data->isExpire());
        $data();
        $this->assertTrue($data->isExpire());
    }


    /**
     * @covers \loeye\base\ContextData::expire
     * @todo   Implement testExpire().
     */
    public function testExpire()
    {
        $actual = 1;
        $data = ContextData::init($actual);
        $this->assertFalse($data->isExpire());
        $data();
        $this->assertTrue($data->isExpire());
        $data->expire(2);
        $this->assertFalse($data->isExpire());
        $data();
        $this->assertFalse($data->isExpire());
    }


    /**
     * @covers \loeye\base\ContextData::getExpire
     * @todo   Implement testGetExpire().
     */
    public function testGetExpire()
    {
        $actual = 1;
        $data = ContextData::init($actual);
        $this->assertEquals($data->getExpire(), $actual);
        $actual1 = 3;
        $data->expire($actual1);
        $this->assertEquals($data->getExpire(), $actual1);
    }

}
