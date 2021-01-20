<?php
require __DIR__ . "/WeChatPay/vendor/autoload.php";

if (!defined("WHMCS")) {
    die("This file cannot be accessed directly");
}

/**
 * Define module related meta data.
 *
 * Values returned here are used to determine module related capabilities and
 * settings.
 *
 * @see https://developers.whmcs.com/payment-gateways/meta-data-params/
 *
 * @return array
 */
function wechatpay_MetaData()
{
    return array(
        'DisplayName' => '微信支付',
        'APIVersion' => '1.1', // Use API Version 1.1
        'DisableLocalCreditCardInput' => true,
        'TokenisedStorage' => false,
    );
}

/**
 * Define gateway configuration options.
 *
 * The fields you define here determine the configuration options that are
 * presented to administrator users when activating and configuring your
 * payment gateway module for use.
 *
 * Supported field types include:
 * * text
 * * password
 * * yesno
 * * dropdown
 * * radio
 * * textarea
 *
 * Examples of each field type and their possible configuration parameters are
 * provided in the sample function below.
 *
 * @return array
 */
function wechatpay_config()
{
    return array(
        // the friendly display name for a payment gateway should be
        // defined here for backwards compatibility
        'FriendlyName' => array(
            'Type' => 'System',
            'Value' => '微信支付',
        ),
        'appId' => array(
            'FriendlyName' => '应用ID',
            'Type' => 'text',
            'Size' => '32',
            'Default' => '',
            'Description' => '直连商户申请的公众号或移动应用appid',
        ),
        'merchantId' => array(
            'FriendlyName' => '直连商户号',
            'Type' => 'text',
            'Size' => '32',
            'Default' => '',
            'Description' => '直连商户的商户号，由微信支付生成并下发',
        ),
        'serialNo' => array(
            'FriendlyName' => '商户API证书序列号',
            'Type' => 'text',
            'Size' => '64',
            'Default' => '',
            'Description' => '',
        ),
        'privateKey' => array(
            'FriendlyName' => '商户私钥',
            'Type' => 'textarea',
            'Rows' => '3',
            'Cols' => '60',
            'Description' => '',
        ),
        'APIv3Key' => array(
            'FriendlyName' => 'APIv3秘钥',
            'Type' => 'password',
            'Size' => '64',
            'Default' => '',
            'Description' => '',
        ),
        'invoiceIdPrefix' => array(
            'FriendlyName' => '订单号前缀',
            'Type' => 'text',
            'Size' => '8',
            'Default' => 'WXPAY',
            'Description' => '加在WHMCS账单ID开头的字符串，通过这个可以解决多系统使用同一个商户出现订单号重复的问题。<span style="color: red;">更改前请确定无未回调的订单，否则将导致未回调的订单出现异常。</span>',
        ),
    );
}

/**
 * Payment link.
 *
 * Required by third party payment gateway modules only.
 *
 * Defines the HTML output displayed on an invoice. Typically consists of an
 * HTML form that will take the user to the payment gateway endpoint.
 *
 * @param array $params Payment Gateway Module Parameters
 *
 * @return string
 * @see https://developers.whmcs.com/payment-gateways/third-party-gateway/
 *
 */
function wechatpay_link($params)
{
    $wechatPay = new \YunInternet\WHMCS\WeChatPay\WHMCSWeChatPay($params["appId"], $params["merchantId"], $params["serialNo"], $params["privateKey"], $params["APIv3Key"]);
    $api = $wechatPay->createAPI();

    $systemUrl = $params['systemurl'];
    $wechatPayParams = $api->getParamBuilder()
        ->set("appid", $params["appId"])
        ->set("mchid", $params["merchantId"])
        ->set("description", $params['companyname'] . " invoice #" . $params['invoiceid'])
        ->set("out_trade_no", \YunInternet\WHMCS\WeChatPay\WHMCSWeChatPay::invoiceId2OutTradeNo($params['invoiceid'], $params["invoiceIdPrefix"]))
        ->set("notify_url", $systemUrl . '/modules/gateways/callback/wechatpay.php')
        ->set("amount", [
            "total" => intval($params['amount'] * 100),
            "currency" => $params['currency'],
        ])
        ->build();

    try {
        $link = $api->nativePay($wechatPayParams);

        return "
<style type='text/css'>
#wechat-pay-qr-code {
    display: inline-block; margin: 0 auto;
}
</style>
<div id='wechat-pay-qr-code'>
</div>
<div><img src='{$systemUrl}/modules/gateways/WeChatPay/WePayIcon.png' alt='微信支付' style='width: 33px; height: 29px; margin-right: 5px;'>微信扫码付款</div>" . "
<script type='text/javascript' src='{$systemUrl}/modules/gateways/WeChatPay/qrcode.min.js'></script>
<script type='text/javascript'>
" . '
$(function () {
var qrcode = new QRCode(document.getElementById("wechat-pay-qr-code"), {
    text: "' . $link . '",
    width: 128,
    height: 128,
});
function checkInvoiceStatus() {
$.ajax("' . $systemUrl . '/modules/gateways/WeChatPay/invoiceStatus.php", {
dataType: "json",
data: "invoiceId='. $params["invoiceid"] .'",
method: "post",
success: function (data) {
if (data.result) {
location.reload();
} else {
setTimeout(checkInvoiceStatus, 3000);
}
},
});
}
checkInvoiceStatus();
});
' . "
</script>
";
    } catch (\YunInternet\WHMCS\WeChatPay\Exceptions\WeChatPayException $weChatPayException) {
        logActivity("message: " . $weChatPayException->getMessage() . ", code: " . $weChatPayException->getCode() . ", file:" . $weChatPayException->getFile() . ", line: #" . $weChatPayException->getLine() . "\n" . $weChatPayException->getTraceAsString(), 0);
        return "<div style='color: red;'>二维码生成失败</div>";
    }
}
