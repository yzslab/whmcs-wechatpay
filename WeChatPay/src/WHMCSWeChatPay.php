<?php


namespace YunInternet\WHMCS\WeChatPay;


use WechatPay\GuzzleMiddleware\Auth\WechatPay2Validator;
use WechatPay\GuzzleMiddleware\Util\PemUtil;
use YunInternet\WHMCS\WeChatPay\CertificateGetters\WeChatPayAPIv3CertificateGetter;
use YunInternet\WHMCS\WeChatPay\CertificateRepositories\WHMCSDBCertificateRepository;
use YunInternet\WHMCS\WeChatPay\Exceptions\InvalidOutTradeNoException;
use YunInternet\WHMCS\WeChatPay\Exceptions\WeChatPayException;

class WHMCSWeChatPay
{
    private $appId;

    private $merchantId;

    private $serialNo;

    private $privateKey;

    private $APIv3Key;

    private $clientFactory;

    private $certificateManager;

    /**
     * WeChatPayAPIv3 constructor.
     * @param string $appId
     * @param string $merchantId
     * @param string $certificateSerialNo
     * @param string $privateKey
     * @param string $APIv3Key
     */
    public function __construct(string $appId, string $merchantId, string $certificateSerialNo, string $privateKey, string $APIv3Key)
    {
        $this->appId = $appId;
        $this->merchantId = $merchantId;
        $this->serialNo = $certificateSerialNo;
        $this->privateKey = PemUtil::loadPrivateKeyFromString($privateKey);
        $this->APIv3Key = $APIv3Key;
        $this->clientFactory = new WeChatPayMiddlewareClientFactory($this->merchantId, $this->serialNo, $this->privateKey);
        $this->certificateManager = new CertificateManager(new WHMCSDBCertificateRepository(), new WeChatPayAPIv3CertificateGetter($this->APIv3Key, $this->clientFactory));
    }

    public function createAPI(): WeChatPayAPIv3
    {
        $client = $this->clientFactory->create(new WechatPay2Validator(new CertificateVerifier($this->certificateManager)));
        return new WeChatPayAPIv3($client);
    }

    public function createValidator(): NotificationValidator
    {
        return new NotificationValidator($this->APIv3Key, new CertificateVerifier($this->certificateManager));
    }

    /**
     * @return array
     * @throws Exceptions\WeChatPayException
     */
    public function notificationValidate(): array
    {
        return $this->createValidator()->validate($_SERVER["HTTP_WECHATPAY_TIMESTAMP"], $_SERVER["HTTP_WECHATPAY_NONCE"], $_SERVER["HTTP_WECHATPAY_SERIAL"], $_SERVER["HTTP_WECHATPAY_SIGNATURE"], file_get_contents('php://input'));
    }

    /**
     * @param string|int $invoiceId
     * @param string $prefix
     * @return string
     */
    public static function invoiceId2OutTradeNo($invoiceId, string $prefix): string
    {
        return $prefix . date("ymdhis") . "T" . $invoiceId;
    }

    /**
     * @param string $outTradeNo
     * @param string $prefix
     * @return int
     * @throws InvalidOutTradeNoException
     */
    public static function outTradeNo2InvoiceId(string $outTradeNo, string $prefix): int
    {
        $prefixLength = strlen($prefix);
        $prefixTotalLength = $prefixLength + 12;
        if (strlen($outTradeNo) <= $prefixTotalLength) {
            throw new InvalidOutTradeNoException("invalid length");
        }
        if (substr($outTradeNo, 0, $prefixLength) !== $prefix) {
            throw new InvalidOutTradeNoException("invalid prefix");
        }
        if ($outTradeNo[$prefixTotalLength] !== "T") {
            throw new InvalidOutTradeNoException("invalid format");
        }
        $invoiceId = substr($outTradeNo, $prefixTotalLength + 1);
        if (is_numeric($invoiceId) === false) {
            throw new InvalidOutTradeNoException("not an numeric");
        }
        return intval($invoiceId);
    }
}