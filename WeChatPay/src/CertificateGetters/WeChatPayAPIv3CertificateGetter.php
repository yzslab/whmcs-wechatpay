<?php

namespace YunInternet\WHMCS\WeChatPay\CertificateGetters;


use WechatPay\GuzzleMiddleware\Auth\CertificateVerifier;
use WechatPay\GuzzleMiddleware\Auth\WechatPay2Validator;
use WechatPay\GuzzleMiddleware\Util\AesUtil;
use YunInternet\WHMCS\WeChatPay\Certificate;
use YunInternet\WHMCS\WeChatPay\Contracts\CertificateGetter;
use YunInternet\WHMCS\WeChatPay\Exceptions\CertificateGetterException;
use YunInternet\WHMCS\WeChatPay\NoopValidator;
use YunInternet\WHMCS\WeChatPay\WeChatPayAPIv3;
use YunInternet\WHMCS\WeChatPay\WeChatPayMiddlewareClientFactory;

class WeChatPayAPIv3CertificateGetter implements CertificateGetter
{
    private $APIv3Key;

    private $clientFactory;

    /**
     * CertificateDownloader constructor.
     * @param string $APIv3Key
     * @param WeChatPayMiddlewareClientFactory $clientFactory
     */
    public function __construct(string $APIv3Key, WeChatPayMiddlewareClientFactory $clientFactory)
    {
        $this->APIv3Key = $APIv3Key;
        $this->clientFactory = $clientFactory;
    }


    /**
     * @return CertificateVerifier[]
     * @throws CertificateGetterException
     */
    public function get(): array
    {
        $client = $this->clientFactory->create(new NoopValidator());
        $api = new WeChatPayAPIv3($client);

        $list = $api->getCertificate($resp);

        $plainCerts = [];
        $x509Certs = [];

        $certificateData = [];

        $decrypter = new AesUtil($this->APIv3Key);
        foreach ($list['data'] as $item) {
            $encCert = $item['encrypt_certificate'];
            $plain = $decrypter->decryptToString($encCert['associated_data'],
                $encCert['nonce'], $encCert['ciphertext']);
            if (!$plain) {
                throw new CertificateGetterException("decrypted certificate unsuccessfully");
            }
            // 通过加载对证书进行简单合法性检验
            $cert = \openssl_x509_read($plain); // 从字符串中加载证书
            if (!$cert) {
                throw new CertificateGetterException("parse decrypted certificate unsuccessfully");
            }
            $plainCerts[] = $plain;
            $x509Certs[] = $cert;
            $certificateData[] = new Certificate($item["serial_no"], $plain, (new \DateTime($item["effective_time"]))->format("Y-m-d H:i:s"), (new \DateTime($item["expire_time"]))->format("Y-m-d H:i:s"));
        }
        // 使用下载的证书再来验证一次应答的签名
        $validator = new WechatPay2Validator(new CertificateVerifier($x509Certs));
        if (!$validator->validate($resp)) {
            throw new CertificateGetterException("response validated unsuccessfully");
        }
        return $certificateData;
    }
}