<?php
require __DIR__ . "/../WeChatPay/vendor/autoload.php";


// Require libraries needed for gateway module functions.
require_once __DIR__ . '/../../../init.php';
require_once __DIR__ . '/../../../includes/gatewayfunctions.php';
require_once __DIR__ . '/../../../includes/invoicefunctions.php';

use \WHMCS\Database\Capsule;

// Detect module name from filename.
$gatewayModuleName = "wechatpay";

// Fetch gateway configuration parameters.
$gatewayParams = getGatewayVariables($gatewayModuleName);

// Die if module is not active.
if (!$gatewayParams['type']) {
    die("Module Not Activated");
}

$wechatPay = new \YunInternet\WHMCS\WeChatPay\WHMCSWeChatPay($gatewayParams["appId"], $gatewayParams["merchantId"], $gatewayParams["serialNo"], $gatewayParams["privateKey"], $gatewayParams["APIv3Key"]);

$success = false;
$transactionStatus = "Failure";
$remarks = [];

$params = null;
try {
    $params = $wechatPay->notificationValidate();
    $success = true;
    $transactionStatus = "Success";

    $invoiceId = \YunInternet\WHMCS\WeChatPay\WHMCSWeChatPay::outTradeNo2InvoiceId($params["out_trade_no"], $gatewayParams["invoiceIdPrefix"]);

    /**
     * Validate Callback Invoice ID.
     *
     * Checks invoice ID is a valid invoice number. Note it will count an
     * invoice in any status as valid.
     *
     * Performs a die upon encountering an invalid Invoice ID.
     *
     * Returns a normalised invoice ID.
     *
     * @param int $invoiceId Invoice ID
     * @param string $gatewayName Gateway Name
     */
    $invoiceId = checkCbInvoiceID(intval($invoiceId), $gatewayParams['name']);

    /**
     * Check Callback Transaction ID.
     *
     * Performs a check for any existing transactions with the same given
     * transaction number.
     *
     * Performs a die upon encountering a duplicate.
     *
     * @param string $transactionId Unique Transaction ID
     */
    checkCbTransID($params["transaction_id"]);

    if ($params["trade_state"] === "SUCCESS") {
        $paymentAmount = $params["amount"]["payer_total"] / 100;

        // $gatewayParams['convertto']非空表示WHMCS发生了货币转换
        if ($gatewayParams['convertto']) {
            // 获取用户的货币
            $userId = Capsule::table("tblinvoices")->where("id", $invoiceId)->first()->userid;
            $userCurrencyId = getCurrency($userId)["id"];

            if ($gatewayParams['convertto'] != $userCurrencyId) {
                $remarks[] = "convert currency from " . $gatewayParams['convertto'] . " to " . $userCurrencyId;
                $paymentAmount = convertCurrency($paymentAmount, $gatewayParams['convertto'], $userCurrencyId);
            }
        }

        /**
         * Add Invoice Payment.
         *
         * Applies a payment transaction entry to the given invoice ID.
         *
         * @param int $invoiceId Invoice ID
         * @param string $transactionId Transaction ID
         * @param float $paymentAmount Amount paid (defaults to full balance)
         * @param float $paymentFee Payment fee (optional)
         * @param string $gatewayModule Gateway module name
         */
        addInvoicePayment(
            $invoiceId,
            $params["transaction_id"],
            $paymentAmount,
            0,
            $gatewayModuleName
        );
        echo json_encode([
            "code" => "SUCCESS",
            "message" => "",
        ]);
    }
} catch (\YunInternet\WHMCS\WeChatPay\Exceptions\InvalidSignatureException $e) {
    $success = false;
    $remarks[] = \YunInternet\WHMCS\WeChatPay\Common::throwable2String($e);
    $transactionStatus = "invalid signature";
} catch (\YunInternet\WHMCS\WeChatPay\Exceptions\DecryptException $e) {
    $success = false;
    $remarks[] = \YunInternet\WHMCS\WeChatPay\Common::throwable2String($e);
    $transactionStatus = "decrypted unsuccessfully";
} catch (\YunInternet\WHMCS\WeChatPay\Exceptions\InvalidOutTradeNoException $e) {
    $success = false;
    $remarks[] = \YunInternet\WHMCS\WeChatPay\Common::throwable2String($e);
    $transactionStatus = "invalid out_trade_no: " . $e->getMessage();
} catch (\YunInternet\WHMCS\WeChatPay\Exceptions\WeChatPayException $e) {
    $success = false;
    $remarks[] = \YunInternet\WHMCS\WeChatPay\Common::throwable2String($e);
    $transactionStatus = "internal error";
} finally {
    $dumpRequest = [
        "url" => $_SERVER['REQUEST_URI'],
        "headers" => [],
        "headerMap" => [],
        "body" => file_get_contents('php://input'),
        "decrypted" => $params,
        "remarks" => $remarks,
    ];
    foreach ($_SERVER as $key => $value) {
        if (strncmp("HTTP_", $key, 5) === 0) {
            $header = substr($key, 5);
            if (array_key_exists($key, $dumpRequest["headers"]) === false) {
                $dumpRequest["headers"][$header] = [];
            }
            $dumpRequest["headers"][$header][] = $value;
            $dumpRequest["headerMap"][$header] = $value;
        }
    }

    /**
     * Log Transaction.
     *
     * Add an entry to the Gateway Log for debugging purposes.
     *
     * The debug data can be a string or an array. In the case of an
     * array it will be
     *
     * @param string $gatewayName Display label
     * @param string|array $debugData Data to log
     * @param string $transactionStatus Status
     */
    logTransaction($gatewayParams['name'], $dumpRequest, $transactionStatus);


    if ($success !== true) {
        http_response_code(403);
        echo json_encode([
            "code" => "VALIDATION_UNSUCCESSFULLY",
            "message" => $transactionStatus,
        ]);
    }
}

