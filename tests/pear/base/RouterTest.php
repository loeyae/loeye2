<?php

namespace loeye\unit\base;

use loeye\base\Exception;
use loeye\base\Router;
use loeye\error\BusinessException;
use loeye\unit\TestCase;

/**
 * Generated by PHPUnit_SkeletonGenerator on 2020-01-08 at 03:31:49.
 */
class RouterTest extends TestCase
{

    /**
     * @var Router
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     * @throws BusinessException
     */
    protected function setUp()
    {
        $property = 'unit';
        $this->object = new Router($property);
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {

    }

    /**
     * @cover Router::getRouter
     * @todo   Implement testGetRouter().
     */
    public function testGetRouter()
    {
        $this->assertNotNull($this->object->getRouter('home'));
        $this->assertNotNull($this->object->getRouter('admin_home'));
        $this->assertNotNull($this->object->getRouter('admin'));
        $this->assertNull($this->object->getRouter('test'));
    }

    /**
     * @cover Router::getRouterKey
     * @todo   Implement testGetRouterKey().
     */
    public function testGetRouterKey()
    {
        $this->assertNotNull($this->object->getRouterKey('http://localhost/admin/user/add.html'));
        $this->assertEquals('admin', $this->object->getRouterKey('http://localhost/admin/user/add.html'));
        $this->assertEquals('admin_home', $this->object->getRouterKey('http://localhost/admin/'));
        $this->assertNull($this->object->getRouterKey('http://localhost/user/add'));
    }

    /**
     * @cover Router::match
     * @todo   Implement testMatch().
     */
    public function testMatch()
    {
        $this->assertNotNull($this->object->match('http://localhost/admin/user/add.html'));
        $this->assertEquals('loeyae.admin.user.add', $this->object->match('http://localhost/admin/user/add.html'));
        $this->assertEquals('loeyae.admin.homepage', $this->object->match('http://localhost/admin/'));
        $this->assertEquals('loeyae.frontend.user', $this->object->match('http://localhost/user/'));
        $this->assertEquals('user.add', $this->object->match('http://localhost/user/add.html'));
        $this->assertNotEmpty($this->object->getMatchedData());
        $this->assertNotEmpty($this->object->getSettings());
        $this->assertArrayHasKey('module', $this->object->getSettings());
        $this->assertArrayHasKey('prop', $this->object->getSettings());
        $this->assertEquals('#^/(?<module>\w+)/(?<prop>\w+).html$#', $this->object->getMatchedRule());
        $this->assertEmpty($this->getProvidedData());
        $this->assertEquals('loeyae.user.get', $this->object->match('http://localhost/loeyae/user/get/1.html'));
        $this->assertNotEmpty($this->object->getMatchedData());
        $this->assertNotEmpty($this->object->getSettings());
        $this->assertNotEmpty($this->object->getPathVariable());
        $this->assertArrayHasKey('id', $this->object->getPathVariable());
        $this->assertNull($this->object->match('http://localhost/user/add'));
    }

    /**
     * @cover Router::generate
     * @todo   Implement testGenerate().
     */
    public function testGenerate()
    {
        $e = null;
        try {
            $this->assertNotNull($this->object->generate('index', ['prop' => 'user']));
        } catch (Exception $exc) {
            $e = $exc;
        }
        $this->assertNull($e);
        $this->assertEquals('/user/', $this->object->generate('index', ['prop' => 'user']));
        define('BASE_SERVER_URL', 'http://localhost');
        $this->assertEquals('http://localhost/user/', $this->object->generate('index', ['prop' => 'user']));
        $e = null;
        try {
            $this->object->generate('test');
        } catch (Exception $exc) {
            $e = $exc;
        }
        $this->assertNotNull($e);
        $this->assertInstanceOf(BusinessException::class, $e);
    }

}
