<?php

/**
 * TestCase.php
 *
 * @author  Zhang Yi <loeyae@gmail.com>
 * @version 2020年1月7日 下午9:46:32
 */
namespace loeye\unit;

use PHPUnit\Framework\TestCase as BaseTestCase;

/**
 * TestCase
 *
 * @author   Zhang Yi <loeyae@gmail.com>
 */
class TestCase extends BaseTestCase 
{
    
    public static function setUpBeforeClass() {
        echo "befor class";
    }
    
    protected function setUp() {
        echo "set up";
    }
    
    public static function tearDownAfterClass() {
        echo "after class";
    }
    
    protected function tearDown() {
        echo "tear down";
    }
    
    
}
