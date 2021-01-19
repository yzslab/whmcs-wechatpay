<?php


namespace YunInternet\WHMCS\WeChatPay;


class WeChatPayParamBuilder
{
    private $params = [];

    public function set(string $key, $value)
    {
        $this->params[$key] = $value;
        return $this;
    }

    public function build(): array
    {
        return $this->params;
    }
}