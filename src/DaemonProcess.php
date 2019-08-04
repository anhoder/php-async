<?php
/**
 * Class DaemonProcess
 * 守护进程
 *
 * @source    DaemonProcess.php
 * @package   Async
 * @author    AlanAlbert <alan1766447919@gmail.com>
 * @version   v1.0.0	Sunday, July 28th, 2019.
 * @copyright Copyright (c) 2019, AlanAlbert
 * @license   MIT License
 */
namespace Async;

use Async\Contract\JobInterface;
use Async\Exception\EnvException;
use Async\Exception\ForkException;
use Async\Exception\FileException;
use Async\Exception\SessionException;
use Async\Exception\JobException;
use Async\Exception\PidsFileException;
use Async\Logger;
use \Throwable;

/**
 * 创建守护程序
 * 
 */
class DaemonProcess
{
    /**
     * @var		string	PID_DIR             PID目录
     */
    const PID_DIR           = '/tmp/php-async-pid/';

    /**
     * @var		string	PIDS_FILE             存各个守护程序的信息
     */
    const PIDS_FILE         = 'php-async.pids';        

    /**
     * @var		string	STATUS_RUNNING      进程运行状态：运行中
     */
    const STATUS_RUNNING    = 'RUNNING';  

    /**
     * @var		string	STATUS_STOPPED      进程运行状态：已停止
     */
    const STATUS_STOPPED    = 'STOPPED';                
    
    /**
     * @var		string	STATUS_COMPLETED    进程运行状态：已完成
     */
    const STATUS_COMPLETED  = 'COMPLETED';              

    /**
     * @var		string|null	$pidFile        守护进程的pid文件，格式为/var/run/php-async-{$pid}.pid
     */
    protected $pidFile      = null;

    /**
     * @var		string|null	$job            异步任务
     */
    protected $job          = null;                     

    /**
     * 构造函数
     *
     * @param JobInterface $job
     * @return void
     */
    public function __construct(JobInterface $job)
    {
        try {
            $this->checkEnv();
            $this->job = $job;
        } catch (Throwable $e) {
            Logger::log(Logger::TYPE_ERROR, $e->getMessage());
            die($e->getMessage());
        }
    }

    /**
     * 检验是否安装pcntl扩展
     * 
     * @return void 
     * @throws EnvException 运行环境错误
     */
    protected function checkEnv()
    {
        if (!function_exists('pcntl_fork')) {
            throw new EnvException('require extension: pcntl');
        }
    }
    
    /**
     * 创建并执行守护程序
     *
     * @return void
     * @throws ForkException|SessionException
     */
    public function run()
    {
        try {
            $pid = pcntl_fork();
            if ($pid < 0) {         // 创建失败
                throw new ForkException('Fork process error.');
            } elseif ($pid === 0) { // 子进程执行代码
                // 分离Session
                if (posix_setsid() == -1) {
                    throw new SessionException('Separate session error.');
                }
                // chdir('/');
                umask(0);
            
                // 创建pid文件
                $this->createPidFile();
            
                // 更新pids文件
                $this->updatePidsFile(self::STATUS_RUNNING);

                // 写入日志
                Logger::log(Logger::TYPE_RUNNING, 'Process(' . posix_getpid() . ') start running');
            
                // 运行主任务及回调函数
                $this->job->job();
                $this->job->callback();
            
                // 杀死守护进程
                $this->kill();
            } else {
                // 父进程忽略子进程的结束，将回收权交给内核init
                pcntl_signal(SIGCHLD, SIG_IGN);
            }
        } catch (Throwable $e) {
            Logger::log(Logger::TYPE_ERROR, $e->getMessage());
            die($e->getMessage());
        }
    }
    
    /**
     * 创建pid文件
     *
     * @return void
     * @throws FileException
     */
    protected function createPidFile()
    {
        $pid = posix_getpid();
        if (!is_dir(self::PID_DIR)) {
            mkdir(self::PID_DIR);
        }
        $this->pidFile = self::PID_DIR . 'php-async-' . $pid . '.pid';
        if (!file_put_contents($this->pidFile, $pid)) {
            throw new FileException('Write pid file error (' . $this->pidFile . ')');
        }
    }

    /**
     * 删除pid文件
     *
     * @return void
     * @throws FileException
     */
    protected function removePidFile()
    {
        if (!is_file($this->pidFile)) {
            throw new FileException('Pid file not exists');
        }
        if (!unlink($this->pidFile)) {
            throw new FileException('Remove pid file error');
        }
    }

    /**
     * 更新pids文件
     *
     * @param string $status
     * @return void
     * @throws PidsFileException|FileException|Exception
     */
    protected function updatePidsFile($status)
    {
        $pid = posix_getpid();
        if (is_file(self::PID_DIR . self::PIDS_FILE)) {
            $content = file_get_contents(self::PID_DIR . self::PIDS_FILE);
            $jobs = json_decode($content, true) ? json_decode($content, true) : [];
        } else {
            $jobs = [];
        }
        switch ($status) {
            case self::STATUS_RUNNING:
                $jobs[$pid] = [
                    'status'        => $status,
                    'creat_time'    => date('Y-m-d H:i:s'),
                    'over_time'     => ''
                ];
                break;
            case self::STATUS_STOPPED:
            case self::STATUS_COMPLETED:
                if (!isset($jobs[$pid])) {
                    throw new PidsFileException('Pids file error');
                }
                $jobs[$pid]['status']       = $status;
                $jobs[$pid]['over_time']    = date('Y-m-d H:i:s');
                break;
            default:
                throw new Exception('Status error');
        }
        $content = json_encode($jobs);
        if (!file_put_contents(self::PID_DIR . self::PIDS_FILE, $content)) {
            throw new FileException('Write file error (' . self::PIDS_FILE . ')');
        }
    }

    /**
     * 杀死当前进程
     *
     * @return void
     */
    protected function kill()
    {
        $this->updatePidsFile(self::STATUS_COMPLETED);
        $this->removePidFile();
        Logger::log(Logger::TYPE_COMPLETED, 'Process(' . posix_getpid() . ') is completed');
        posix_kill(posix_getpid(), SIGTERM);
    }
}
