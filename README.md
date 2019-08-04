# php-async

PHP异步回调的简单实现

## Requirement / 依赖

* ext-pcntl
* php > 5.6

## Installation / 安装

```sh
composer require alanalbert/php-async
```

## Usage / 使用

> 该库可以工作在php-fpm或cli模式下。（但是，每个异步任务会创建一个进程进行处理，直至任务完成后才会被**彻底杀死**）

### 使用实例

```php
require './vendor/autoload.php';

use Async\DaemonProcess;
use Async\Job;

// 异步操作任务
$job = new Job();
// 设置异步任务
$job->setJob(function () {
  sleep(5);		
});
// 设置回调函数
$job->setCallback(function () {
  file_put_contents('处理完成.txt', '处理完成');		
});

// 实例化异步进程
$daemon = new DaemonProcess($job);
$daemon->run();
```

### Async\DaemonProcess

守护进程类，该类提供的方法有：

```php
// 构造函数，接收实现Async\Contract\JobInterface接口的类的实例
__construct(Async\Contract\JobInterface $job): void

// 运行守护进程
run(): void
```

### Async\Contract\JobInterface

异步任务接口，该接口很简单，只需要实现两个方法即可：

```php
// 异步任务
job(): void
// 回调任务
callback(): void
```

### Async\Job

该类实现了`Async\Contract\JobInterface`接口，使用起来更方便，其提供的方法有：

```php
// 设置异步任务
// $job可以为callable或Closure类型
// $params为异步任务需要使用的参数，会在执行时，传入给异步任务
setJob($job, $params = null): void

// 设置回调函数
// $callback可以为callable或Closure类型
// $params为回调函数需要使用的参数，会在执行时，传入给回调函数
setCallback($callback, $params = null): void
```

### 其他

* 在使用该库执行异步任务时，会生成日志文件，位于`/tmp/php-async-log/`下
* 如果不使用`Async\Job`类，也可以自定义类并实现`Async\Contract\JobInterface`接口

## TODO

* 命令行工具，查看异步任务的状态，停止异步任务的运行，查看日志...
* ...
