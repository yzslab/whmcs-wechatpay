<?php


namespace YunInternet\WHMCS\WeChatPay\Contracts;


use YunInternet\WHMCS\WeChatPay\Certificate;
use YunInternet\WHMCS\WeChatPay\Exceptions\CertificateNotFoundException;

interface CertificateRepository
{
    /**
     * @param string $merchantId
     * @param Certificate[] $certificates
     * @return void
     */
    public function storeCertificates(array $certificates);

    /**
     * @var string $merchantId
     * @return Certificate[]
     */
    public function getEffectiveCertificates(): array;

    /**
     * @param string $serialNo
     * @return Certificate
     * @throws CertificateNotFoundException
     */
    public function getCertificate(string $serialNo): Certificate;
}