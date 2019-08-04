<?php
/**
 * interface JobInterface
 * 异步任务接口
 *
 * @source    JobInterface.php
 * @package   Async\Contract
 * @author    AlanAlbert <alan1766447919@gmail.com>
 * @version   v1.0.0	Sunday, July 28th, 2019.
 * @copyright Copyright (c) 2019, AlanAlbert
 * @license   MIT License
 */
namespace Async\Contract;

/**
 * JobInterface，异步任务接口
 * 
 */
interface JobInterface
{
    /**
     * 异步任务
     *
     * @return void
     */
    public function job();

    /**
     * 回调函数
     *
     * @return void
     */
    public function callback();
}