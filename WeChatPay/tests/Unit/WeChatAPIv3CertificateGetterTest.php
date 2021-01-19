<?php


namespace YunInternet\WHMCS\WeChatPay\Tests\Unit;


use PHPUnit\Framework\TestCase;
use YunInternet\WHMCS\WeChatPay\CertificateGetters\WeChatPayAPIv3CertificateGetter;
use YunInternet\WHMCS\WeChatPay\WeChatPayMiddlewareClientFactory;

class WeChatAPIv3CertificateGetterTest extends TestCase
{
    public function testGetCertificate()
    {
        $getter = new WeChatPayAPIv3CertificateGetter($_SERVER["APIv3Key"], new WeChatPayMiddlewareClientFactory($_SERVER["merchantId"], $_SERVER["serialNo"], openssl_get_privatekey(file_get_contents($_SERVER["privateKey"]))));
        $this->assertNotEmpty($certificates = $getter->get());
        var_dump($certificates);
    }
}