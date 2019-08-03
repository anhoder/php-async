<?php
namespace Async;

use Async\Contract\JobInterface;
use \Closure;
use \BadFunctionCallException;

/**
 * Job类，实现JobInterface接口
 * class Job
 */
class Job implements JobInterface
{
    /**
     * 需要执行的任务
     * @var		mixed	$job
     */
    protected $jobFunc        = null;

    /**
     * 任务需要的参数
     * @var		mixed	$jobParams
     */
    protected $jobParams      = null;

    /**
     * 任务执行完之后的返回值，传入回调函数的第一个参数
     * @var		mixed	$jobReturn
     */
    protected $jobReturn      = null; 

    /**
     * 回调函数
     * @var		mixed	$callback
     */
    protected $callbackFunc   = null;

    /**
     * 回调函数的参数，传入回调函数的第二个参数
     * @var		mixed	$callbackParams
     */
    protected $callbackParams = null;

    /**
     * 实现JobInterface的job方法，指定主工作任务
     *
     * @return void
     */
    public function job(): void
    {
        if ($this->jobFunc instanceof Closure) {
            $this->jobReturn = ($this->jobFunc)();
        } else {
            $this->jobReturn = call_user_func($this->jobFunc, $this->jobParams);
        }
    }

    /**
     * 实现JobInterface的callback方法，指定回调函数
     *
     * @return void
     */
    public function callback(): void
    {
        if ($this->callbackFunc instanceof Closure) {
            ($this->callbackFunc)($this->jobReturn);
        } else {
            call_user_func($this->callbackFunc, $this->jobReturn, $this->callbackParams);
        }
    }

    /**
     * 设置主任务
     *
     * @param Closure | callable $job
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
     * @param Closure | callable $callback
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