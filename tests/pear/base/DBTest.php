<?php
/**
 * DBTest.php
 *
 * PHP version 7
 *
 * Licensed under the Apache License, Version 2.0 (the "License"),
 * see LICENSE for more details: http://www.apache.org/licenses/LICENSE-2.0.
 *
 * @category PHP
 * @package  LOEYE
 * @author   Zhang Yi <loeyae@gmail.com>
 * @version  2020/4/20 14:32
 * @link     https://github.com/loeyae/loeye2.git
 */

namespace loeye\unit\base;

use Doctrine\ORM\EntityManager;
use loeye\base\DB;
use loeye\unit\TestCase;

class DBTest extends TestCase
{

    /**
     * @covers \loeye\base\DB::getInstance
     * @covers \loeye\base\DB::__construct
     * @covers \loeye\base\DB::_getEntityManager
     * @covers \loeye\base\DB::getCache
     */
    public function testInstance()
    {
        $db = DB::getInstance($this->appConfig);
        $db1 = DB::getInstance($this->appConfig);
        $this->assertSame($db, $db1);
        $db2 = new DB($this->appConfig);
        $this->assertNotSame($db, $db2);
        $db3 = DB::getInstance($this->appConfig, 'default');
        $this->assertSame($db, $db3);
    }

    /**
     * @covers \loeye\base\DB::getInstance
     * @covers \loeye\base\DB::__construct
     * @covers \loeye\base\DB::_getEntityManager
     * @expectedException \loeye\error\BusinessException
     * @expectedExceptionMessage 无效的数据库设置
     */
    public function testInstanceWithNoExistsType()
    {
        $db = DB::getInstance($this->appConfig, 'mysql');
    }

    /**
     * @covers \loeye\base\DB::getInstance
     * @covers \loeye\base\DB::__construct
     * @covers \loeye\base\DB::_getEntityManager
     * @expectedException \loeye\error\BusinessException
     * @expectedExceptionMessage 无效的数据库类型
     */
    public function testInstancewthBlankType()
    {
        $db = DB::getInstance($this->appConfig, '');
    }

    /**
     * @covers \loeye\base\DB::entityManager
     * @covers \loeye\base\DB::em
     */
    public function testEntityManager()
    {
        $db = DB::getInstance($this->appConfig);
        $em = $db->em();
        $entityManager = $db->entityManager();
        $this->assertSame($entityManager, $em);
        $this->assertInstanceOf(EntityManager::class, $em);
    }

}
