<?php
namespace Async;

use Async\Contract\JobInterface;
use Async\Exception\EnvException;
use Async\Exception\ForkException;
use Async\Exception\FileException;
use Async\Exception\SessionException;
use Async\Exception\JobException;

class DaemonProcess
{
    const PIDS_FILE         = './php-async.pids';    // 保存各个守护程序的信息
    const LOG_DIR           = '/tmp/php-async/';        // 守护程序的日志文件所在目录

    const STATUS_RUNNING    = 'running';                // 进程运行状态：运行中
    const STATUS_STOP       = 'stopped';                // 进程运行状态：已停止
    const STATUS_COMPLETE   = 'completed';              // 进程运行状态：已完成

    private $pidFile        = null;                     // 守护进程的pid文件，格式为/var/run/php-async-{$pid}.pid
    private $logFile        = null;                     // 日志文件，使用日期(2019-08-03)作为文件名
    private $job            = null;

    public function __construct(JobInterface $job)
    {
        $this->checkEnv();
        $this->job = $job;
    }

    /**
     * 检验是否安装pcntl扩展
     * @return void 
     * @throws EnvException 运行环境错误
     */
    private function checkEnv()
    {
        if (!function_exists('pcntl_fork')) {
            throw new EnvException('require extension pcntl');
        }
    }
    
    /**
     * 创建并执行守护程序
     *
     * @return void
     */
    public function run()
    {
        $pid = pcntl_fork();
        if ($pid < 0) {         // 创建失败
            throw new ForkException('Fork process error.');
        } elseif ($pid > 0) { // 父进程执行代码
            exit;
        }

        // 子进程执行
        // 分离Session
        if (posix_setsid() == -1) {
            throw new SessionException('Separate session error.');
        }
        // chdir('/');
        umask(0);
    
        // 写入pid文件
        $pid = posix_getpid();
        $this->pidFile = './php-async-' . $pid . '.pid';
        if (!file_put_contents($this->pidFile, $pid)) {
            throw new FileException('Write pid file error (' . $this->pidFile . ')');
        }
    
        // 更新pids文件
        if (file_exists(self::PIDS_FILE)) {
            $content = file_get_contents(self::PIDS_FILE);
            $jobs = json_decode($content) ?? [];
        } else {
            $jobs = [];
        }
        array_push($jobs, [
            'pid'           => $pid,
            'status'        => self::STATUS_RUNNING,
            'creat_time'    => date('Y-m-d H:i:s'),
            'over_time'     => ''
        ]);
        $content = json_encode($jobs);
        if (!file_put_contents(self::PIDS_FILE, $content)) {
            throw new FileException('Write file error (' . self::PIDS_FILE . ')');
        }
    
        // 运行主任务及回调函数
        $this->job->job();
        $this->job->callback();
    
        // 杀死守护进程
        posix_kill($pid, SIGTERM);
    }
    
    /**
     * 启动守护进程
     *
     * @return void
     */
    private function start()
    {
        if ($this->getPid()) {
            $this->tips('进程正在运行...' . PHP_EOL);
            return;
        }
        $this->tips('启动成功' . PHP_EOL);
        $this->daemonize();
    }

    /**
     * 停止进程
     *
     * @return void
     */
    private function stop()
    {
        if ($pid = $this->getPid()) {
            posix_kill($pid, SIGTERM);
            unlink($this->pid_file);
            $this->tips('停止成功, Bye~' . PHP_EOL);
        } else {
            $this->tips('进程未运行~.~' . PHP_EOL);
        }
    }

    /**
     * 获取状态
     *
     * @return void
     */
    private function status()
    {
        if ($this->getPid()) {
            $this->tips('进程正在运行...' . PHP_EOL);
        } else {
            $this->tips('进程已停止...' . PHP_EOL);
        }
    }

    /**
     * 输出提示信息
     *
     * @param string $msg 提示内容
     * @return void
     */
    private function tips($msg)
    {
        printf("%s: %s\n", date('Y-m-d H:i:s'), $msg);
    }

    /**
     * 获取守护进程pid
     *
     * @return void
     */
    private function getPid()
    {
        if (!file_exists($this->pid_file)) {
            return 0;
        }
        $pid = intval(file_get_contents($this->pid_file));
        if (posix_kill($pid, SIG_DFL)) {
            return $pid;
        } else {
            unlink($this->pid_file);
            return 0;
        }
    }

    /**
     * 运行
     *
     * @return void
     */
    public function run1($argv)
    {
        if (count($argv) < 2) {
            $this->tips('Params: stop | start | status');
            return;
        }
        switch ($argv[1]) {
            case 'start':
                $this->start();
                break;
            case 'stop':
                $this->stop();
                break;
            case 'status':
                $this->status();
                break;
            default:
                $this->tips('Params: stop | start | status');
                break;
        }
    }
}
