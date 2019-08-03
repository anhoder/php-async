<?php
namespace Async\Contract;

interface JobInterface
{
    /**
     * 任务
     *
     * @return void
     */
    public function job(): void;

    /**
     * 回调函数
     *
     * @return void
     */
    public function callback(): void;
}