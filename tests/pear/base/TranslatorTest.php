<?php

namespace loeye\unit\base;

use loeye\base\AppConfig;
use loeye\base\Translator;
use loeye\unit\TestCase;

/**
 * Generated by PHPUnit_SkeletonGenerator on 2020-02-03 at 01:46:52.
 */
class TranslatorTest extends TestCase
{

    /**
     * @var Translator
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $appConfig = new AppConfig('unit');
        $this->object = new Translator($appConfig);
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {

    }

    /**
     * @cover Translator::getLocale
     * @todo   Implement testGetLocale().
     */
    public function testGetLocale()
    {
        $expected = 'zh_CN';
        $actual = $this->object->getLocale();
        $this->assertEquals($expected, $actual);
    }

    /**
     * @cover Translator::getString
     * @todo   Implement testGetString().
     */
    public function testGetString()
    {
        $expected = '业务错误';
        $actual = $this->object->getString('Business Error', [], 'error');
        $this->assertEquals($expected, $actual);
        $expected = '无效的配置：test';
        $actual = $this->object->getString('Invalid config: %setting%', ['%setting%' => 'test'], 'error');
        $this->assertEquals($expected, $actual);
        $expected = 'Invalid config: test';
        $actual = $this->object->getString('Invalid config: %setting%', ['%setting%' => 'test']);
        $this->assertEquals($expected, $actual);
        $expected = '单元测试';
        $actual = $this->object->getString('unit test');
        $this->assertEquals($expected, $actual);
        $expected = '单元测试';
        $actual = $this->object->getString('unit test', ['unit' => 'test']);
        $this->assertEquals($expected, $actual);
    }

    /**
     * @cover Translator::getReplacedString
     * @todo   Implement testGetReplacedString().
     */
    public function testGetReplacedString()
    {
        $expected = '无效的配置：test';
        $actual = $this->object->getReplacedString('Invalid config setting: %setting%', '%setting%', 'test');
        $this->assertEquals($expected, $actual);
    }

    /**
     * @cover Translator::getFormatString
     * @todo   Implement testGetFormatString().
     */
    public function testGetFormatString()
    {
        $expected = 'Invalid config: test';
        $actual = $this->object->getFormatString('Invalid config: {0}', ['test']);
        $this->assertEquals($expected, $actual);
    }

}
