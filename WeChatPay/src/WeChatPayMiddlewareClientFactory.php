<?php


namespace YunInternet\WHMCS\WeChatPay;


use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use WechatPay\GuzzleMiddleware\Validator;
use WechatPay\GuzzleMiddleware\WechatPayMiddleware;

class WeChatPayMiddlewareClientFactory
{
    private $merchantId;

    private $serialNo;

    private $privateKey;

    /**
     * APIv3ClientFactory constructor.
     * @param string $merchantId
     * @param string $serialNo
     * @param resource $privateKey
     */
    public function __construct(string $merchantId, string $serialNo, $privateKey)
    {
        $this->merchantId = $merchantId;
        $this->serialNo = $serialNo;
        $this->privateKey = $privateKey;
    }

    public function create(Validator $validator): Client
    {
        $wechatpayMiddleware = WechatPayMiddleware::builder()
            ->withMerchant($this->merchantId, $this->serialNo, $this->privateKey)
            ->withValidator($validator)
            ->build();

        $stack = HandlerStack::create();
        $stack->push($wechatpayMiddleware, 'wechatpay');
        return new Client(['handler' => $stack]);
    }
}