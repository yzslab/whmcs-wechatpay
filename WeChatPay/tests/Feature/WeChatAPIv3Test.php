<?php


namespace YunInternet\WHMCS\WeChatPay\Tests\Feature;


use PHPUnit\Framework\TestCase;
use WechatPay\GuzzleMiddleware\Auth\WechatPay2Validator;
use YunInternet\WHMCS\WeChatPay\CertificateGetters\WeChatPayAPIv3CertificateGetter;
use YunInternet\WHMCS\WeChatPay\CertificateRepositories\HashTableCertificateRepository;
use YunInternet\WHMCS\WeChatPay\CertificateVerifier;
use YunInternet\WHMCS\WeChatPay\WeChatPayMiddlewareClientFactory;
use YunInternet\WHMCS\WeChatPay\CertificateManager;
use YunInternet\WHMCS\WeChatPay\WeChatPayAPIv3;

class WeChatAPIv3Test extends TestCase
{
    public function buildClient()
    {
        $merchantId = $_SERVER["merchantId"];
        $serialNo = $_SERVER["serialNo"];
        $privateKey = openssl_get_privatekey(file_get_contents($_SERVER["privateKey"]));
        $APIv3Key = $_SERVER["APIv3Key"];
        $clientFactory = new WeChatPayMiddlewareClientFactory($merchantId, $serialNo, $privateKey);
        $certificateManager = new CertificateManager(new HashTableCertificateRepository(), new WeChatPayAPIv3CertificateGetter($APIv3Key, $clientFactory));
        return $clientFactory->create(new WechatPay2Validator(new CertificateVerifier($certificateManager)));
    }

    public function buildWeChatPayAPIv3()
    {
        return new WeChatPayAPIv3($this->buildClient());
    }

    public function testNativePay()
    {
        $api = $this->buildWeChatPayAPIv3();
        $params = $api->getParamBuilder()
            ->set("appid", $_SERVER["appId"])
            ->set("mchid", $_SERVER["merchantId"])
            ->set("description", "WeChatPay APIv3 test")
            ->set("out_trade_no", date("YmdHis") . mt_rand(100, 999))
            ->set("notify_url", $_SERVER["notifyURL"])
            ->set("amount", [
                "total" => 10,
                "currency" => "CNY",
            ])
            ->build()
        ;
        $this->assertTrue(strncmp("weixin://", $url = $api->nativePay($params), 9) === 0);
        var_dump($url);
    }

    public function testH5Pay()
    {
        $api = $this->buildWeChatPayAPIv3();
        $params = $api->getParamBuilder()
            ->set("appid", $_SERVER["appId"])
            ->set("mchid", $_SERVER["merchantId"])
            ->set("description", "WeChatPay APIv3 test")
            ->set("out_trade_no", date("YmdHis" . mt_rand(100, 999)))
            ->set("notify_url", $_SERVER["notifyURL"])
            ->set("amount", [
                "total" => 10,
                "currency" => "CNY",
            ])
            ->set("scene_info", [
                "payer_client_ip" => "192.168.1.1",
                "h5_info" => [
                    "type" => "iOS",
                ],
            ])
            ->build()
        ;
        $this->assertTrue(strncmp("https://", $url = $api->h5Pay($params), 8) === 0);
        var_dump($url);
    }
}