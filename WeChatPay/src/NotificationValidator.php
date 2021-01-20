<?php


namespace YunInternet\WHMCS\WeChatPay;


use WechatPay\GuzzleMiddleware\Auth\Verifier;
use WechatPay\GuzzleMiddleware\Util\AesUtil;
use YunInternet\WHMCS\WeChatPay\Exceptions\DecryptException;
use YunInternet\WHMCS\WeChatPay\Exceptions\InvalidSignatureException;
use YunInternet\WHMCS\WeChatPay\Exceptions\WeChatPayException;

class NotificationValidator
{
    private $APIv3Token;

    private $verifier;

    public function __construct(string $APIv3Key, Verifier $verifier)
    {
        $this->APIv3Token = $APIv3Key;
        $this->verifier = $verifier;
    }

    /**
     * @param string $timestamp
     * @param string $nonce
     * @param string $serialNo
     * @param string $signature
     * @param string $body
     * @return array
     */
    public function validate(string $timestamp, string $nonce, string $serialNo, string $signature, string $body): array
    {
        $message = "$timestamp\n$nonce\n$body\n";
        if ($this->verifier->verify($serialNo, $message, $signature) !== true) {
            throw new InvalidSignatureException("invalid notification signature");
        }

        $decodedData = json_decode($body, true);
        $resource = $decodedData["resource"];

        $decrypter = new AesUtil($this->APIv3Token);
        $plain = $decrypter->decryptToString($resource['associated_data'],
            $resource['nonce'], $resource['ciphertext']);
        if ($plain === false) {
            throw new DecryptException("notification ciphertext decrypted unsuccessfully");
        }
        return json_decode($plain, true);
    }
}