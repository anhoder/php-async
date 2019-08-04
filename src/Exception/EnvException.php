<?php
/**
 * EnvException
 * 运行环境异常
 *
 * @source    EnvException.php
 * @package   Async\Exception
 * @author    AlanAlbert <alan1766447919@gmail.com>
 * @version   v1.0.0	Sunday, July 28th, 2019.
 * @copyright Copyright (c) 2019, AlanAlbert
 * @license   MIT License
 */
namespace Async\Exception;

use \Exception;

/**
 * 运行环境异常
 * 
 */
class EnvException extends Exception
{
    /**
     * @var		string	$message 异常信息
     */
    protected $message  = 'Runtime Environment Error';


    /**
     * 构造函数
     *
     * @param string $message
     * @param integer $code
     * @param Throwable $previous
     */
    public function __construct($message = '', $code = 0, $previous = null)
    {
        if ($message) {
            $message = $this->message . '(' . $message . ')';
        }
        parent::__construct($message, $code, $previous);
    }
}