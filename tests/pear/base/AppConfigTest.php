<?php

namespace loeye\unit\base;

use loeye\base\AppConfig;
use PHPUnit\Framework\TestCase;

/**
 * Generated by PHPUnit_SkeletonGenerator on 2020-01-19 at 09:41:07.
 */
class AppConfigTest extends TestCase
{

    /**
     * @var AppConfig
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $_ENV['LOEYE_PROFILE_ACTIVE'] = 'dev';
        $this->object = new AppConfig('unit');
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {
        unset($this->object);
    }

    /**
     * @covers \loeye\base\AppConfig::offsetExists
     */
    public function testOffsetExists(): void
    {
        $this->assertTrue(isset($this->object['constants']));
    }

    /**
     * @covers \loeye\base\AppConfig::offsetGet
     */
    public function testOffsetGet(): void
    {

        $actual = $this->object['constants.BASE_SERVER_URL'];
        $expected = 'http://localhost:8088';
        $this->assertEquals($expected, $actual);
    }

    /**
     * @covers \loeye\base\AppConfig::offsetSet
     */
    public function testOffsetSet()
    {

        $this->object['constants.BASE_SERVER_URL'] = 'http://localhost:8089';
        $actual = $this->object['constants.BASE_SERVER_URL'];
        $expected = 'http://localhost:8088';
        $this->assertEquals($expected, $actual);
    }

    /**
     * @covers \loeye\base\AppConfig::offsetUnset
     */
    public function testOffsetUnset()
    {
        unset($this->object['constants.BASE_SERVER_URL']);
        $actual = $this->object['constants.BASE_SERVER_URL'];
        $expected = 'http://localhost:8088';
        $this->assertEquals($expected, $actual);
    }

    /**
     * @covers \loeye\base\AppConfig::getSetting
     */
    public function testGetSetting()
    {
        $actual = $this->object->getSetting('constants.BASE_SERVER_URL');
        $expected = 'http://localhost:8088';
        $this->assertEquals($expected, $actual);
    }

    /**
     * @covers \loeye\base\AppConfig::setPropertyName
     */
    public function testSetPropertyName()
    {
        $this->object->setPropertyName('unit');
        $actual = $this->object->getPropertyName();
        $expected = 'unit';
        $this->assertEquals($expected, $actual);
    }

    /**
     * @covers \loeye\base\AppConfig::getPropertyName
     */
    public function testGetPropertyName()
    {
        $actual = $this->object->getPropertyName();
        $expected = 'unit';
        $this->assertEquals($expected, $actual);
    }

    /**
     * @covers \loeye\base\AppConfig::setTimezone
     */
    public function testSetTimezone()
    {
        $this->object->setTimezone('Asia/Chongqing');
        $actual = $this->object->getTimezone();
        $expected = 'Asia/Chongqing';
        $this->assertEquals($expected, $actual);
    }

    /**
     * @covers \loeye\base\AppConfig::getTimezone
     */
    public function testGetTimezone()
    {
        $actual = $this->object->getTimezone();
        $expected = 'Asia/Shanghai';
        $this->assertEquals($expected, $actual);
    }

    /**
     * @covers \loeye\base\AppConfig::setLocale
     */
    public function testSetLocale()
    {
        $this->object->setLocale('en_US');
        $actual = $this->object->getLocale();
        $expected = 'en_US';
        $this->assertEquals($expected, $actual);
    }

    /**
     * @covers \loeye\base\AppConfig::getLocale
     */
    public function testGetLocale()
    {
        $actual = $this->object->getLocale();
        $expected = 'zh_CN';
        $this->assertEquals($expected, $actual);
    }

}
