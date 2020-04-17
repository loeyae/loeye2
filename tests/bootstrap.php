<?php

/**
 * bootstrap.php
 * 
 * @author  Zhang Yi <loeyae@gmail.com>
 * @version SVN: $Id: Zhang Yi $
 */

require_once(dirname(__DIR__) .DIRECTORY_SEPARATOR .'vendor'. DIRECTORY_SEPARATOR .'autoload.php');

define('PROJECT_UNIT_DIR', __DIR__);
define('PROJECT_UNIT_RUNTIME_DIR', PROJECT_UNIT_DIR.DIRECTORY_SEPARATOR.'runtime');

//dirname(PROJECT_UNIT_DIR) .'/output/code-coverage-report');use SebastianBergmann\CodeCoverage\CodeCoverage;
////use SebastianBergmann\CodeCoverage\Report\Clover;
////use SebastianBergmann\CodeCoverage\Report\Html\Facade;
////
////$coverage = new CodeCoverage();
////
////$coverage->filter()->addDirectoryToWhitelist(dirname(PROJECT_UNIT_DIR) .'/pear');
////
////$coverage->start('<name of test>');
////
////$coverage->stop();
////
////$writer = new Clover;
////$writer->process($coverage, dirname(PROJECT_UNIT_DIR) .'/output/coverage/clover.xml');
////
////$writer = new Facade;
////$writer->process($coverage,