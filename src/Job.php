<?php
/**
 * Class Job 
 * 异步任务，实现Async\Contract\JobInterface接口
 *
 * @source    Job.php
 * @package   Async
 * @author    AlanAlbert <alan1766447919@gmail.com>
 * @version   v1.0.0	Sunday, July 28th, 2019.
 * @copyright Copyright (c) 2019, AlanAlbert
 * @license   MIT License
 */
namespace Async;

use Async\Contract\JobInterface;
use \Closure;
use \BadFunctionCallException;

/**
 * 异步任务，实现Async\Contract\JobInterface接口
 * 
 */
class Job implements JobInterface
{
    /**
     * 需要执行的任务
     * @var		callable|Closure|null	$job
     */
    protected $jobFunc        = null;

    /**
     * 任务需要的参数
     * @var		array|null	$jobParams
     */
    protected $jobParams      = null;

    /**
     * 任务执行完之后的返回值，传入回调函数的第一个参数
     * @var		mixed	$jobReturn
     */
    protected $jobReturn      = null; 

    /**
     * 回调函数
     * @var		callable|Closure|null	$callback
     */
    protected $callbackFunc   = null;

    /**
     * 回调函数的参数，传入回调函数的第二个参数
     * @var		array|null	$callbackParams
     */
    protected $callbackParams = null;

    /**
     * 实现JobInterface的job方法，指定主工作任务
     *
     * @return void
     */
    public function job()
    {
        if ($this->jobFunc instanceof Closure) {
            $jobFunc = $this->jobFunc;
            $this->jobReturn = $jobFunc($this->jobParams);
        } else {
            Logger::log(Logger::TYPE_RUNNING, '开始运行异步任务...');
            $this->jobReturn = call_user_func($this->jobFunc, $this->jobParams);
            Logger::log(Logger::TYPE_COMPLETED, '异步任务执行完毕.');
        }
    }

    /**
     * 实现JobInterface的callback方法，指定回调函数
     *
     * @return void
     */
    public function callback()
    {
        if ($this->callbackFunc instanceof Closure) {
            $callbackFunc = $this->callbackFunc;
            $callbackFunc($this->jobReturn, $this->callbackParams);
        } else {
            Logger::log(Logger::TYPE_RUNNING, '开始执行回调函数...');
            call_user_func($this->callbackFunc, $this->jobReturn, $this->callbackParams);
            Logger::log(Logger::TYPE_COMPLETED, '回调函数执行完毕.');
        }
    }

    /**
     * 设置主任务
     *
     * @param Closure|callable $job
     * @param array $params
     * @return void
     */
    public function setJob($job, array $params = null)
    {
        if (!($job instanceof Closure) && !is_callable($job)) {
            throw new BadFunctionCallException('Function or method (' . $job . ') is not found');
        }
        $this->jobFunc = $job;
        if ($params) {
            $this->jobParams = $params;
        }
    }

    /**
     * 设置回调函数
     *
     * @param Closure|callable $callback
     * @param array $params
     * @return void
     */
    public function setCallback($callback, array $params = null)
    {
        if (!($callback instanceof Closure) && !is_callable($callback)) {
            throw new BadFunctionCallException('Function or method (' . $callback . ') is not found');
        }
        $this->callbackFunc = $callback;
        if ($params) {
            $this->callbackParams = $params;
        }
    }

}