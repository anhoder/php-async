<?php

require './vendor/autoload.php';

use Async\Job;
use Async\DaemonProcess;

$job = new Job();
$job->setJob(function () {
    sleep(10);
});
$job->setCallback(function () {
    file_put_contents('job_over.txt', '');
});
$daemon = new DaemonProcess($job);
$daemon->run();
echo "测试";