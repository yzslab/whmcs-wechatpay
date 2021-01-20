<?php


namespace YunInternet\WHMCS\WeChatPay\Tests\Unit;


use PHPUnit\Framework\TestCase;
use YunInternet\WHMCS\WeChatPay\WHMCSWeChatPay;

class WHMCSWeChatPayTest extends TestCase
{
    const PREFIX = "WXPAY";

    const INVOICE_ID = 12356;

    private $date;

    protected function setUp()
    {
        $this->date = date("ymdhis");
    }


    public function testInvoiceIdConvertor()
    {
        $outTradeNo = WHMCSWeChatPay::invoiceId2OutTradeNo(self::INVOICE_ID, self::PREFIX);
        $this->assertEquals(self::PREFIX, substr($outTradeNo, 0, 5));
        $this->assertEquals("T", substr($outTradeNo, 5 + 12, 1));
        $this->assertEquals((string) self::INVOICE_ID, substr($outTradeNo, 5 + 13));
        $this->assertEquals(self::INVOICE_ID, WHMCSWeChatPay::outTradeNo2InvoiceId($outTradeNo, self::PREFIX));
    }

    public function testLengthCheck()
    {
        $this->expectExceptionMessage("invalid length");
        $outTradeNo = self::PREFIX . $this->date;
        WHMCSWeChatPay::outTradeNo2InvoiceId($outTradeNo, self::PREFIX);
    }

    public function testPrefixCheck()
    {
        $this->expectExceptionMessage("invalid prefix");
        $outTradeNo = "QQPAY" . $this->date . "T" . self::PREFIX;
        WHMCSWeChatPay::outTradeNo2InvoiceId($outTradeNo, self::PREFIX);
    }

    public function testFormatCheck()
    {
        $this->expectExceptionMessage("invalid format");
        $outTradeNo = self::PREFIX . $this->date . "A" . self::INVOICE_ID;
        WHMCSWeChatPay::outTradeNo2InvoiceId($outTradeNo, self::PREFIX);
    }

    public function testNumericCheck()
    {
        $this->expectExceptionMessage("not an numeric");
        $outTradeNo = self::PREFIX . $this->date . "TA" . self::INVOICE_ID;
        WHMCSWeChatPay::outTradeNo2InvoiceId($outTradeNo, self::PREFIX);
    }
}