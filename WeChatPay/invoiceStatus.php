<?php
require_once __DIR__ . '/../../../init.php';

use \Illuminate\Database\Capsule\Manager as Capsule;

$result = false;
$message = "";

$ca = new WHMCS_ClientArea();

$userid = $ca->getUserID();

if ($userid <= 0) {
    $message = "unauthenticated";
    goto SEND_RESPONSE;
}

$invoiceId = $_POST['invoiceId'];
$invoice = Capsule::table('tblinvoices')->where('id', $invoiceId)->where('userid', $userid)->first();
if (is_null($invoice)) {
    $message = "invoice not found";
    goto SEND_RESPONSE;
}

$status = $invoice->status;
if ($invoice->status !== "Paid") {
    goto SEND_RESPONSE;
}
$result = true;
SEND_RESPONSE:
echo json_encode([
    "result" => $result,
    "message" => $message,
]);