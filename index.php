<?php
require './vendor/autoload.php';

use Async\DaemonProcess;
use Async\Job;

// 异步操作任务
$job = new Job();
// 设置异步任务
$job->setJob(function () {
  sleep(100);
});
// 设置回调函数
$job->setCallback(function () {
  file_put_contents('处理完成.txt', '处理完成');
});

// 实例化异步进程
$daemon = new DaemonProcess($job);
$daemon->run();
