<?php


namespace YunInternet\WHMCS\WeChatPay\Tests\Feature;


use PHPUnit\Framework\TestCase;
use YunInternet\WHMCS\WeChatPay\CertificateGetters\WeChatPayAPIv3CertificateGetter;
use YunInternet\WHMCS\WeChatPay\CertificateRepositories\HashTableCertificateRepository;
use YunInternet\WHMCS\WeChatPay\CertificateVerifier;
use YunInternet\WHMCS\WeChatPay\NotificationValidator;
use YunInternet\WHMCS\WeChatPay\CertificateManager;
use YunInternet\WHMCS\WeChatPay\WeChatPayMiddlewareClientFactory;

class NotificationValidatorTest extends TestCase
{
    public function testValidate()
    {
        $merchantId = $_SERVER["merchantId"];
        $serialNo = $_SERVER["serialNo"];
        $privateKey = openssl_get_privatekey(file_get_contents($_SERVER["privateKey"]));
        $APIv3Key = $_SERVER["APIv3Key"];
        $certificateManager = new CertificateManager(new HashTableCertificateRepository(), new WeChatPayAPIv3CertificateGetter($APIv3Key, new WeChatPayMiddlewareClientFactory($merchantId, $serialNo, $privateKey)));
        $notificationValidator = new NotificationValidator($APIv3Key, new CertificateVerifier($certificateManager));
        var_dump($notificationValidator->validate($_SERVER["timestamp"], $_SERVER["nonce"], $_SERVER["serialNo"], $_SERVER["signature"], $_SERVER["body"]));
        $this->assertTrue(true);
    }
}