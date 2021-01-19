<?php


namespace YunInternet\WHMCS\WeChatPay\CertificateGetters;


use YunInternet\WHMCS\WeChatPay\Contracts\CertificateGetter;

class MockCertificateGetter implements CertificateGetter
{
    private $certificates;

    private $returnWhen;

    public $invokeCounter = 0;

    public function __construct($certificates, $returnWhen = 1)
    {
        $this->certificates = $certificates;
        $this->returnWhen = $returnWhen;
    }

    public function get(): array
    {
        ++$this->invokeCounter;
        if ($this->invokeCounter >= $this->returnWhen) {
            return $this->certificates;
        }
        return [];
    }
}