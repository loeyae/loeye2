<?php

/**
 * TestCase.php
 *
 * @author  Zhang Yi <loeyae@gmail.com>
 * @version 2020年1月7日 下午9:46:32
 */
namespace loeye\unit;

use loeye\base\AppConfig;
use PHPUnit\Framework\TestCase as BaseTestCase;

/**
 * TestCase
 *
 * @author   Zhang Yi <loeyae@gmail.com>
 */
class TestCase extends BaseTestCase 
{

    /**
     * @var AppConfig AppConfig
     */
    protected $appConfig;

    public static function setUpBeforeClass() {
        ;
    }
    
    protected function setUp() {
        $this->appConfig = new AppConfig('unit');
    }
    
    public static function tearDownAfterClass() {
        ;
    }
    
    protected function tearDown() {
        unset($this->appConfig);
    }
    
    
}
