<?php
require_once __DIR__ . '/app/bootstrap.php';
Auth::requireLogin();
$gateway = PaymentGatewayFactory::create('payhere');
$userId = (int)Auth::id();
$paymentId = (int)Helpers::get('payment_id', '0');
if (!$paymentId && Helpers::get('order_id')) {
    $paymentId = $gateway->paymentIdFromOrder(Helpers::get('order_id'));
}
$paymentModel = new PaymentModel();
$payment = $paymentModel->find($paymentId);
if (!$payment || (int)$payment['user_id'] !== $userId) {
    Flash::error('Payment record not found.');
    Helpers::redirect('dashboard.php');
}
if ($payment['status'] === 'completed') {
    Flash::info('This payment was already confirmed.');
    Helpers::redirect('dashboard.php');
}
if ($payment['status'] === 'failed') {
    Flash::error('This payment was not successful. You have not been charged.');
    Helpers::redirect('subscription.php');
}
$demo = Helpers::get('demo') === '1';
$transactionId = '';
$verified = false;
if ($demo && !$gateway->enabled()) {
    $verified = true;
    $transactionId = 'DEMO_' . strtoupper(bin2hex(random_bytes(6)));
} elseif ($gateway->enabled()) {
    $fresh = $paymentModel->find($paymentId);
    if ($fresh['status'] === 'completed') {
        Flash::success('Payment confirmed. Thank you!');
        Helpers::redirect('dashboard.php');
    }
    if (Config::get('PAYHERE_TRUST_RETURN')) {
        $verified = true;
        $transactionId = $gateway->orderId($paymentId);
    }
}
if (!$verified) {
    Flash::info('We are waiting for the payment gateway to confirm this payment. It will activate automatically once confirmed.');
    Helpers::redirect('dashboard.php');
}
$result = PaymentFulfillment::complete($payment, $transactionId);
if ($result['ok']) {
    Flash::success($result['message']);
    Helpers::redirect('dashboard.php');
}
Flash::error($result['message']);
Helpers::redirect('subscription.php');
