<?php
/**
 * ContextTest.php
 *
 * PHP version 7
 *
 * Licensed under the Apache License, Version 2.0 (the "License"),
 * see LICENSE for more details: http://www.apache.org/licenses/LICENSE-2.0.
 *
 * @category PHP
 * @package  LOEYE
 * @author   Zhang Yi <loeyae@gmail.com>
 * @version  2020/4/18 14:24
 * @link     https://github.com/loeyae/loeye2.git
 */

namespace loeye\unit\base;

use loeye\base\AppConfig;
use loeye\base\Context;
use loeye\base\Exception;
use loeye\base\ModuleDefinition;
use loeye\base\Router;
use loeye\base\UrlManager;
use loeye\client\ParallelClientManager;
use loeye\web\Request;
use loeye\web\Response;
use loeye\web\Template;
use PHPUnit\Framework\TestCase;

class ContextTest extends TestCase
{

    /**
     * @covers \loeye\base\Context::offsetExists
     * @covers \loeye\base\Context::offsetSet
     * @covers \loeye\base\Context::offsetGet
     * @covers \loeye\base\Context::offsetUnset
     * @covers \loeye\base\Context::getAppConfig
     * @covers \loeye\base\Context::setAppConfig
     * @covers \loeye\base\Context::addErrors
     * @covers \loeye\base\Context::__construct
     * @covers \loeye\base\Context::get
     */
    public function testOffset()
    {
        $context = new Context();
        $this->assertFalse(isset($context['test']));
        $this->assertFalse(isset($context['AppConfig']));
        $this->assertFalse(isset($context['errors']));
        $context['test'] = 'sample';
        $context['errors'] = new Exception();
        $context['AppConfig'] = new AppConfig('unit');
        $this->assertTrue(isset($context['test']));
        $this->assertEquals('sample', $context['test']);
        $this->assertEquals('sample', $context->get('test'));
        $this->assertTrue(isset($context['errors']));
        $this->assertIsArray($context['errors']);
        $this->assertEquals(1, count($context['errors']));
        $this->assertTrue(isset($context['AppConfig']));
        $this->assertInstanceOf(AppConfig::class, $context['AppConfig']);
        $context['errors'] = [new \RuntimeException(), new \Exception()];
        $this->assertIsArray($context['errors']);
        $this->assertEquals(3, count($context['errors']));
        unset($context['AppConfig']);
        $this->assertFalse(isset($context['AppConfig']));
        $this->assertNull($context->getAppConfig());
        unset($context['test']);
        $this->assertFalse(isset($context['test']));
        unset($context['errors']);
        $this->assertFalse(isset($context['errors']));
        $this->assertFalse($context->hasErrors());
    }

    /**
     * @covers \loeye\base\Context::__set
     * @covers \loeye\base\Context::__get
     * @covers \loeye\base\Context::__unset
     * @covers \loeye\base\Context::__isset
     * @covers \loeye\base\Context::__construct
     * @covers \loeye\base\Context::isExistKey
     * @covers \loeye\base\Context::isExist
     * @covers \loeye\base\Context::get
     */
    public function testMagic()
    {
        $context = new Context(new AppConfig('unit'));
        $this->assertFalse(isset($context->test));
        $context->test = 'sample';
        $this->assertTrue(isset($context->test));
        $this->assertTrue($context->isExist('test'));
        $this->assertTrue($context->isExistKey('test'));
        $this->assertEquals('sample', $context->test);
        $this->assertEquals('sample', $context->get('test'));
        unset($context->test);
        $this->assertFalse(isset($context->test));
        $this->assertFalse($context->isExist('test'));
        $this->assertFalse($context->isExistKey('test'));
        $this->assertNull($context->test);
        $this->assertEquals('aaa', $context->get('test', 'aaa'));
    }

    /**
     * @covers \loeye\base\Context::setTraceData
     * @covers \loeye\base\Context::getTraceData
     */
    public function testTraceData()
    {
        $context = new Context();
        $context->setTraceData('trace', debug_backtrace());
        $traceData = $context->getTraceData('trace');
        $this->assertIsArray($traceData);
        $this->assertEquals(__CLASS__, $traceData[0]['class']);
    }

    /**
     * @covers \loeye\base\Context::set
     * @covers \loeye\base\Context::get
     * @covers \loeye\base\Context::isEmpty
     * @covers \loeye\base\Context::isExist
     * @covers \loeye\base\Context::getData
     * @covers \loeye\base\Context::getDataGenerator
     * @covers \loeye\base\Context::getWithTrace
     * @covers \loeye\base\Context::unsetKey
     */
    public function testData()
    {
        $context = new Context();
        $context->set('one', 1);
        $context->set('two', 2, 2);
        $context->set('ever', 0, 0);
        $this->assertTrue($context->isExist('one'));
        $this->assertTrue($context->isExist('two'));
        $this->assertTrue($context->isExist('ever'));
        $this->assertFalse($context->isEmpty('one'));
        $this->assertFalse($context->isEmpty('two'));
        $this->assertFalse($context->isEmpty('ever'));
        $this->assertTrue($context->isEmpty('ever', false));
        $data = $context->getData();
        $this->assertIsArray($data);
        $this->assertEquals(3, count($data));
        $this->assertArrayHasKey('one', $data);
        $this->assertArrayHasKey('two', $data);
        $this->assertArrayHasKey('ever', $data);
        $this->assertFalse($context->isEmpty('one'));
        $this->assertFalse($context->isExist('one'));
        $this->assertTrue($context->isExist('two'));
        $this->assertTrue($context->isExist('ever'));
        $this->assertEquals(null, $context->get('one'));
        $this->assertEquals(2, $context->get('two'));
        $this->assertEquals(0, $context->get('ever'));
        $this->assertTrue($context->isEmpty('one'));
        $this->assertFalse($context->isExist('one'));
        $this->assertFalse($context->isExist('two'));
        $this->assertTrue($context->isExist('ever'));
        $this->assertEquals(0, $context->get('ever'));
        $this->assertTrue($context->isExist('ever'));
        $context->set('one',1);
        $this->assertTrue($context->isExist('one'));
        $this->assertEquals(1, $context->getWithTrace('one'));
        $this->assertTrue($context->isExist('one'));
        $this->assertEquals(1, $context->get('one'));
        $this->assertFalse($context->isExist('one'));
        $context->unsetKey('ever');
        $this->assertFalse($context->isExist('ever'));
        $this->assertNull($context->getWithTrace('ever'));
        $context->set('one', 1);
        $context->set('two', 2, 2);
        $context->set('ever', 0, 0);
        $this->assertTrue($context->isExist('one'));
        $this->assertTrue($context->isExist('two'));
        $this->assertTrue($context->isExist('ever'));
        $generator = $context->getDataGenerator();
        $this->assertIsIterable($generator);
        $this->assertTrue($context->isExist('one'));
        $this->assertTrue($context->isExist('two'));
        $this->assertTrue($context->isExist('ever'));
        foreach ($generator as $key => $value) {
            $value();
        }
        $this->assertFalse($context->isExist('one'));
        $this->assertTrue($context->isExist('two'));
        $this->assertTrue($context->isExist('ever'));
    }

    /**
     * @covers \loeye\base\Context::setAppConfig
     * @covers \loeye\base\Context::getAppConfig
     * @covers \loeye\base\Context::setModule
     * @covers \loeye\base\Context::getModule
     * @covers \loeye\base\Context::setParallelClientManager
     * @covers \loeye\base\Context::getParallelClientManager
     * @covers \loeye\base\Context::setRequest
     * @covers \loeye\base\Context::getRequest
     * @covers \loeye\base\Context::setResponse
     * @covers \loeye\base\Context::getResponse
     * @covers \loeye\base\Context::setRouter
     * @covers \loeye\base\Context::getRouter
     * @covers \loeye\base\Context::setTemplate
     * @covers \loeye\base\Context::getTemplate
     */
    public function testObject()
    {
        $context = new Context();
        $this->assertNull($context->getAppConfig());
        $context->setAppConfig(new AppConfig('unit'));
        $this->assertInstanceOf(AppConfig::class, $context->getAppConfig());
        $this->assertNull($context->getModule());
        $context->setModule(new ModuleDefinition($context->getAppConfig(), 'loeyae.login'));
        $this->assertInstanceOf(ModuleDefinition::class, $context->getModule());
        $this->assertNotNull($context->getParallelClientManager());
        $context->setParallelClientManager(new ParallelClientManager());
        $this->assertInstanceOf(ParallelClientManager::class, $context->getParallelClientManager());
        $this->assertNull($context->getRequest());
        $context->setRequest(new Request());
        $this->assertInstanceOf(\loeye\std\Request::class, $context->getRequest());
        $this->assertNull($context->getResponse());
        $context->setResponse(new Response());
        $this->assertInstanceOf(\loeye\std\Response::class, $context->getResponse());
        $this->assertNull($context->getRouter());
        $context->setRouter(new Router($context->getAppConfig()->getPropertyName()));
        $this->assertInstanceOf(\loeye\std\Router::class, $context->getRouter());
        $context->setRouter(new UrlManager($context->getAppConfig()));
        $this->assertInstanceOf(\loeye\std\Router::class, $context->getRouter());
        $this->assertNull($context->getTemplate());
        $context->setTemplate(new Template($context));
        $this->assertInstanceOf(Template::class, $context->getTemplate());
    }

    /**
     * @covers \loeye\base\Context::addErrors
     * @covers \loeye\base\Context::getErrors
     * @covers \loeye\base\Context::removeErrors
     * @covers \loeye\base\Context::hasErrors
     */
    public function testErrors()
    {
        $context = new Context();
        $this->assertFalse($context->hasErrors());
        $context->addErrors('validate_errors', 'field one error');
        $this->assertFalse($context->hasErrors('validate_error'));
        $this->assertTrue($context->hasErrors('validate_errors'));
        $this->assertIsArray($context->getErrors());
        $this->assertEquals(1, count($context->getErrors()));
        $this->assertIsArray($context->getErrors('validate_errors'));
        $this->assertEquals(1, count($context->getErrors('validate_errors')));
        $context->addErrors('validate_errors', 'field two error');
        $this->assertEquals(2, count($context->getErrors('validate_errors')));
        $context->addErrors('validate_error', 'field one error');
        $this->assertTrue($context->hasErrors('validate_error'));
        $this->assertEquals(2, count($context->getErrors()));
        $context->removeErrors('validate_errors');
        $this->assertFalse($context->hasErrors('validate_errors'));
        $this->assertEquals(1, count($context->getErrors()));
        $context->removeErrors('validate_error');
        $this->assertFalse($context->hasErrors('validate_error'));
        $this->assertEquals(0, count($context->getErrors()));
    }

    /**
     * @covers \loeye\base\Context::cacheData
     * @covers \loeye\base\Context::loadCacheData
     * @covers \loeye\base\Context::isExpire
     * @covers \loeye\base\Context::setExpire
     * @covers \loeye\base\Context::getExpire
     */
    public function testCacheData()
    {
        define('PROJECT_PROPERTY', 'unit');
        $context = new Context();
        $context->setRequest(new Request('loeyae.login'));
        $context->setExpire(3);
        $context->set('cache', 'test');
        $context->cacheData();
        $this->assertTrue($context->isExpire('cache'));
        unset($context);
        $context = new Context();
        $context->setRequest(new Request('loeyae.login'));
        $this->assertNull($context->getExpire());
        $context->loadCacheData();
        $this->assertFalse($context->isExpire('cache'));
        sleep(3);
        $context->loadCacheData();
        $context = new Context();
        $context->setRequest(new Request('loeyae.login'));
        $this->assertNull($context->getExpire());
        $context->loadCacheData();
        $this->assertTrue($context->isExpire('cache'));
        $this->assertNull($context->get('cache'));
    }

}
