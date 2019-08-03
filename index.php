<?php

require './vendor/autoload.php';

use Async\Job;
use Async\DaemonProcess;

$job = new Job();
$job->setJob(function (){
    file_put_contents('a.txt', '1234567');
    sleep(5);
});
$job->setCallback(function (){
    file_put_contents('你操作完了.txt', '1234567');
});
$daemon = new DaemonProcess($job);
$daemon->run();