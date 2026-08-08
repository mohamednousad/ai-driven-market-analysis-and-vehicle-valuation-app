<?php
require_once __DIR__ . '/app/bootstrap.php';
Auth::requireLogin();
$paymentId = (int)Helpers::get('payment_id', '0');
$paymentModel = new PaymentModel();
$payment = $paymentModel->find($paymentId);
if ($payment && (int)$payment['user_id'] === (int)Auth::id() && $payment['status'] === 'pending') {
    $paymentModel->fail($paymentId);
}
Flash::warning('Payment cancelled. You have not been charged.');
Helpers::redirect('subscription.php');
