<?php
namespace Async;

class Logger
{
    const TYPE_RUNNING          = 'RUNNING';                // 日志类型：成功
    const TYPE_STOPPED          = 'STOPPED';                // 日志类型：停止
    const TYPE_COMPLETED        = 'COMPLETED';              // 日志类型：完成
    const TYPE_ERROR            = 'ERROR';                  // 日志类型：失败

    const LOG_DIR               = '/tmp/php-async-log/';    // 守护程序的日志文件所在目录

    protected static $logFile   = null;                     // 日志文件，使用日期(2019-08-03)作为文件名

    /**
     * 写入日志文件
     *
     * @param string $type
     * @param string $msg
     * @return void
     * @throws FileException
     */
    public static function log(string $type, string $msg)
    {
        if (!is_dir(self::LOG_DIR)) {
            if (!mkdir(self::LOG_DIR)) {
                throw new FileException('Create dir error');
            }
        }
        self::$logFile = self::LOG_DIR . date('Y-m-d') . '.log';
        $content = '[' . date('Y-m-d H:i:s') . '] ' . $type . ": " . $msg . PHP_EOL;
        if (is_file(self::$logFile)) {
            $content = file_get_contents(self::$logFile) . $content;
        }
        if (!file_put_contents(self::$logFile, $content)) {
            throw new FileException('Write log file error');
        }
    }
}