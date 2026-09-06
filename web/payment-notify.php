<?php
require_once __DIR__ . '/app/bootstrap.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit;
}
$gateway = PaymentGatewayFactory::create('payhere');
if (!$gateway->enabled() || !$gateway->verifyNotify($_POST)) {
    http_response_code(400);
    exit;
}
$paymentId = $gateway->paymentIdFromOrder((string)$_POST['order_id']);
$payment = (new PaymentModel())->find($paymentId);
if (!$payment) {
    http_response_code(404);
    exit;
}
$statusCode = (int)$_POST['status_code'];
if ($statusCode === 2) {
    if (round((float)$_POST['payhere_amount'], 2) !== round((float)$payment['amount'], 2)) {
        (new PaymentModel())->fail($paymentId);
        http_response_code(400);
        exit;
    }
    PaymentFulfillment::complete($payment, (string)($_POST['payment_id'] ?? $gateway->orderId($paymentId)));
} elseif (in_array($statusCode, [-1, -2, -3], true) && $payment['status'] === 'pending') {
    (new PaymentModel())->fail($paymentId);
}
http_response_code(200);
echo 'OK';
