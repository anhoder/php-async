<?php
namespace Async\Exception;

use \Exception;

class EnvException extends Exception
{
    protected $message  = 'Runtime Environment Error';

    public function __construct(string $message = '', int $code = 0, Throwable $previous = null)
    {
        if ($message) {
            $message = $this->message . '(' . $message . ')';
        }
        parent::__construct($message, $code, $previous);
    }
}