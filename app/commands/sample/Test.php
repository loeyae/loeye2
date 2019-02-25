<?php

/**
 * Test.php
 *
 * Licensed under the Apache License, Version 2.0 (the "License"),
 * see LICENSE for more details: http://www.apache.org/licenses/LICENSE-2.0.
 *
 * @author  Zhang Yi <loeyae@gmail.com>
 * @version SVN: $Id: Zhang Yi $
 */
namespace app\commands\sample;

use loeye\console\Command;
use \Symfony\Component\Console\{Input\InputInterface, Output\OutputInterface};
/**
 * Test
 *
 * @author   Zhang Yi <loeyae@gmail.com>
 */
class Test extends Command {

//    protected $name = 'test';
    protected $args = [['conf', 'required' => false, 'help' => 'test args', 'default' => 'dsf']];
    protected $params = [['aaa', 'a', 'required' => false, 'help' => 'test params', 'default' => 'dsf']];

    public function process(InputInterface $input, OutputInterface $output)
    {
//        var_dump($input->getArguments(), $input->getOptions());
        $c1 = new \GuzzleHttp\Client(['base_uri' => 'http://www.baidu.com']);
        $c2 = new \GuzzleHttp\Client(['base_uri' => 'http://www.sogou.com/']);
        $op = [
            $c1->requestAsync('get', '/s?wd=guzzlehttp'),
            $c1->requestAsync('get', '/s?wd=guzzlehttpclinet'),
            $c2->requestAsync('get', '/web?query=guzzlehttp&oq=guzzlehttp'),
            $c2->requestAsync('get', '/web?query=guzzlehttpclinet&oq=guzzlehttpclinet'),
        ];
        $r = \GuzzleHttp\Promise\unwrap($op);
        foreach ($r as $key => $value) {
            $output->write($key, $newline = true);
            $output->write($value->getStatusCode(), $newline = true);
            $output->write($value->getReasonPhrase(), $newline = true);
            $output->write($value->getHeader('Set-Cookie'), $newline = true);
//            $output->write($value->getBody()->getContents(), $newline = true);
        }
        $output->writeln('test a command');
    }
}
