<?php


namespace YunInternet\WHMCS\WeChatPay;


use Psr\Http\Message\ResponseInterface;
use WechatPay\GuzzleMiddleware\Validator;

class NoopValidator implements Validator
{
    public function validate(ResponseInterface $response)
    {
        return true;
    }
}