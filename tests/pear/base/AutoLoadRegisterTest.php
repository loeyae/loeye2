<?php
/**
 * AutoLoadRegisterTest.php
 *
 * PHP version 7
 *
 * Licensed under the Apache License, Version 2.0 (the "License"),
 * see LICENSE for more details: http://www.apache.org/licenses/LICENSE-2.0.
 *
 * @category PHP
 * @package  LOEYE
 * @author   Zhang Yi <loeyae@gmail.com>
 * @version  2020/4/16 17:28
 * @link     https://github.com/loeyae/loeye2.git
 */

namespace loeye\unit\base;

use loeye\base\AutoLoadRegister;
use loeye\unit\TestCase;

class AutoLoadRegisterTest extends TestCase
{

    /**
     * @cover AutoLoadRegister::addAlias
     * @cover AutoLoadRegister::realAliasFile
     */
    public function testAddAlias()
    {
        AutoLoadRegister::addAlias('resource', PROJECT_DIR . D_S . 'resource');
        $this->assertNotNull(AutoLoadRegister::realAliasFile('@resource/unit/messages.zh_CN.yml'));
        $this->assertEquals(PROJECT_DIR . D_S . 'resource' . D_S . 'unit' . D_S . 'messages.zh_CN.yml',
            AutoLoadRegister::realAliasFile('@resource/unit/messages.zh_CN.yml'));
    }

    /**
     * @cover AutoLoadRegister::initApp
     * @cover AutoLoadRegister::realAliasFile
     */
    public function testInitApp()
    {
        $this->assertFalse(AutoLoadRegister::realAliasFile('@conf/unit/app/master.yml'));
        AutoLoadRegister::initApp();
        $this->assertNotNull(AutoLoadRegister::realAliasFile('@conf/unit/app/master.yml'));
        $this->assertEquals(PROJECT_DIR . D_S . 'conf' . D_S . 'unit' . D_S . 'app'.D_S.'master.yml',
            AutoLoadRegister::realAliasFile('@conf/unit/app/master.yml'));
    }

    /**
     * @cover AutoLoadRegister::addNamespace
     * @cover AutoLoadRegister::load
     */
    public function testAddNamespace()
    {
        $this->assertFalse(class_exists('\mock\classes\Test'));
        AutoLoadRegister::addNamespace('mock', PROJECT_DIR.D_S.'mock');
        spl_autoload_register(static function($className) {
            AutoLoadRegister::load($className);
        });
        $this->assertTrue(class_exists('\mock\classes\Test'));
    }

    /**
     * @cover AutoLoadRegister::addDir
     * @cover AutoLoadRegister::load
     */
    public function testAddDir()
    {
        $this->assertFalse(class_exists('\mock\classes\Test'));
        AutoLoadRegister::addDir(PROJECT_DIR);
        spl_autoload_register(static function($className) {
            AutoLoadRegister::load($className);
        });
        $this->assertTrue(class_exists('\mock\classes\Test'));
    }

    /**
     * @cover AutoLoadRegister::loadFile
     */
    public function testLoadFile()
    {
        $this->assertFalse(class_exists('\mock\classes\Test'));
        AutoLoadRegister::loadFile(PROJECT_DIR.D_S.'mock\classes\Test.php');
        $this->assertTrue(class_exists('\mock\classes\Test'));
    }

    /**
     * @cover AutoLoadRegister::addFile
     * @cover AutoLoadRegister::autoLoad
     */
    public function testAddFile()
    {
        $this->assertFalse(class_exists('\mock\classes\Test'));
        AutoLoadRegister::addFile(PROJECT_DIR.D_S.'mock\classes\Test.php');
        AutoLoadRegister::autoLoad();
        $this->assertTrue(class_exists('\mock\classes\Test'));
    }

    /**
     * @cover AutoLoadRegister::addSingle
     * @cover AutoLoadRegister::load
     */
    public function testAddSingle()
    {
        $this->assertFalse(class_exists('\mock\classes\Test'));
        AutoLoadRegister::addSingle('\mock\classes\Test', PROJECT_DIR.D_S.'mock\classes\Test.php');
        spl_autoload_register(static function($className) {
            AutoLoadRegister::load($className);
        });
        $this->assertTrue(class_exists('\mock\classes\Test'));
    }
}
