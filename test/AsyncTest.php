<?php

namespace Async\test;

require_once __DIR__ . '/../vendor/autoload.php';

use PHPUnit\Framework\TestCase;
use Async\Job;
use Async\DaemonProcess;

class AsyncTest extends TestCase
{
    public function testAsync()
    {
        $job = new Job();
        $job->setJob(function () {
            file_put_contents('./test.txt', '123');
        });
        $job->setCallback(function () {
            $content = file_get_contents('./test.txt');
            $this->assertEquals('123', $content);
            unlink('./test.txt');
        });
        $daemon = new DaemonProcess($job);
        $daemon->run();
    }
}