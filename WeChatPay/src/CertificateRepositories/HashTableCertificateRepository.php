<?php


namespace YunInternet\WHMCS\WeChatPay\CertificateRepositories;


use YunInternet\WHMCS\WeChatPay\Certificate;
use YunInternet\WHMCS\WeChatPay\Contracts\CertificateRepository;
use YunInternet\WHMCS\WeChatPay\Exceptions\CertificateNotFoundException;

class HashTableCertificateRepository implements CertificateRepository
{
    private $hashTable = [];

    /**
     * @inheritDoc
     */
    public function storeCertificates(array $certificates)
    {
        $this->hashTable = [];
        foreach ($certificates as $certificate) {
            $this->hashTable[$certificate->serialNo] = $certificate;
        }
    }

    /**
     * @inheritDoc
     */
    public function getEffectiveCertificates(): array
    {
        return array_values($this->hashTable);
    }

    /**
     * @inheritDoc
     */
    public function getCertificate(string $serialNo): Certificate
    {
        if (array_key_exists($serialNo, $this->hashTable)) {
            return $this->hashTable[$serialNo];
        }
        throw new CertificateNotFoundException();
    }
}