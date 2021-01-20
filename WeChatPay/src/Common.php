<?php


namespace YunInternet\WHMCS\WeChatPay;


abstract class Common
{
    public static function throwable2String(\Throwable $throwable)
    {
        return $throwable->getMessage() . ", code: " . $throwable->getCode() . ", file:" . $throwable->getFile() . ", line: #" . $throwable->getLine() . "\n" . $throwable->getTraceAsString();
    }
}