<?php


namespace YunInternet\WHMCS\WeChatPay;


use YunInternet\WHMCS\WeChatPay\Contracts\CertificateGetter;
use YunInternet\WHMCS\WeChatPay\Contracts\CertificateRepository;
use YunInternet\WHMCS\WeChatPay\Exceptions\CertificateNotFoundException;

class CertificateManager
{
    private $certificateRepository;

    private $certificateGetter;

    /**
     * CertificateManager constructor.
     * @param CertificateRepository $certificateRepository
     * @param CertificateGetter $certificateGetter
     */
    public function __construct(CertificateRepository $certificateRepository, CertificateGetter $certificateGetter)
    {
        $this->certificateRepository = $certificateRepository;
        $this->certificateGetter = $certificateGetter;
    }

    /**
     * @return Certificate[]
     */
    public function getAllCertificates(): array
    {
        $certificates = $this->certificateRepository->getEffectiveCertificates();
        if (count($certificates) === 0) {
            $certificates = $this->certificateGetter->get();
            $this->certificateRepository->storeCertificates($certificates);
        }
        return $certificates;
    }

    /**
     * @param string $serialNo
     * @return Certificate
     * @throws CertificateNotFoundException
     * @throws Exceptions\CertificateGetterException
     */
    public function getBySerialNo(string $serialNo): Certificate
    {
        try {
            return $this->certificateRepository->getCertificate($serialNo);
        } catch (CertificateNotFoundException $e) {
            $certificates = $this->certificateGetter->get();
            $this->certificateRepository->storeCertificates($certificates);
        }
        return $this->certificateRepository->getCertificate($serialNo);
    }
}