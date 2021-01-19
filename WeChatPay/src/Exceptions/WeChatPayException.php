<?php


namespace YunInternet\WHMCS\WeChatPay\Exceptions;


use Throwable;

class WeChatPayException extends \Exception
{
    protected $code;

    public function __construct($message = "", $code = 0, Throwable $previous = null)
    {
        $this->code = $code;
        parent::__construct($message, 0, $previous);
    }
}