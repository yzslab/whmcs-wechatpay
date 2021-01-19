<?php


namespace YunInternet\WHMCS\WeChatPay\Contracts;


use YunInternet\WHMCS\WeChatPay\Certificate;
use YunInternet\WHMCS\WeChatPay\Exceptions\CertificateGetterException;

interface CertificateGetter
{
    /**
     * @return Certificate[]
     * @throws CertificateGetterException
     */
    public function get(): array;
}