<?php


namespace YunInternet\WHMCS\WeChatPay;


use WechatPay\GuzzleMiddleware\Auth\Verifier;
use WechatPay\GuzzleMiddleware\Util\PemUtil;
use YunInternet\WHMCS\WeChatPay\Exceptions\WeChatPayException;

class CertificateVerifier implements Verifier
{
    private $certificateManager;

    public function __construct(CertificateManager $certificateManager)
    {
        $this->certificateManager = $certificateManager;
    }

    public function verify($serialNo, $message, $signature)
    {
        $serialNo = \strtoupper(\ltrim($serialNo, '0'));
        $certificate = $this->certificateManager->getBySerialNo($serialNo);
        if (!in_array('sha256WithRSAEncryption', \openssl_get_md_methods(true))) {
            throw new WeChatPayException("当前PHP环境不支持SHA256withRSA");
        }
        $signature = \base64_decode($signature);
        return \openssl_verify($message, $signature, PemUtil::loadCertificateFromString($certificate->certificate),
                'sha256WithRSAEncryption') === 1;
    }
}