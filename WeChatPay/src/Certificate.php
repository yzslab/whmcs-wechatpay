<?php


namespace YunInternet\WHMCS\WeChatPay;


class Certificate
{
    /**
     * @var string
     */
    public $serialNo;

    /**
     * @var string
     */
    public $certificate;

    /**
     * @var string
     */
    public $effectiveTime;

    /**
     * @var string
     */
    public $expireTime;

    /**
     * Certificate constructor.
     * @param string $serialNo
     * @param string $certificate
     * @param string $effectiveTime
     * @param string $expireTime
     */
    public function __construct(string $serialNo, string $certificate, string $effectiveTime, string $expireTime)
    {
        $this->serialNo = $serialNo;
        $this->certificate = $certificate;
        $this->effectiveTime = $effectiveTime;
        $this->expireTime = $expireTime;
    }
}