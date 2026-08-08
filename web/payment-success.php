<?php
require_once __DIR__ . '/app/bootstrap.php';
Auth::requireLogin();
$userId = (int)Auth::id();
$paymentId = (int)Helpers::get('payment_id', '0');
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
$sessionId = Helpers::get('session_id');
$demo = Helpers::get('demo') === '1';
$verified = false;
$transactionId = '';
if ($demo && !StripeClient::enabled()) {
    $verified = true;
    $transactionId = 'DEMO_' . strtoupper(bin2hex(random_bytes(6)));
} elseif ($sessionId && StripeClient::enabled()) {
    $check = StripeClient::retrieveSession($sessionId);
    if ($check['ok'] && ($check['data']['payment_status'] ?? '') === 'paid') {
        $verified = true;
        $transactionId = $sessionId;
    }
}
if (!$verified) {
    $paymentModel->fail($paymentId);
    Flash::error('We could not verify this payment. You have not been charged successfully.');
    Helpers::redirect('subscription.php');
}
$meta = json_decode(Session::get('pending_payment_' . $paymentId, '{}'), true) ?: [];
$notif = new NotificationModel();
if ($payment['category'] === 'subscription') {
    $plan = (new SubscriptionModel())->planById((int)($meta['plan_id'] ?? 0));
    if ($plan) {
        $subId = (new SubscriptionModel())->activate($userId, $plan);
        $paymentModel->complete($paymentId, $transactionId, $subId);
        $notif->push($userId, 'Plan Activated', 'Your ' . $plan['name'] . ' is now active. Enjoy your benefits!', 'payment');
        Flash::success('Payment confirmed. Your ' . $plan['name'] . ' is now active!');
    }
} elseif ($payment['category'] === 'promotion') {
    $adId = (int)($meta['ad_id'] ?? 0);
    $promoType = (string)($meta['promo_type'] ?? 'featured');
    $paymentModel->complete($paymentId, $transactionId);
    (new PromotionModel())->create($adId, $paymentId, $promoType, (float)$payment['amount'], (int)Config::get('PROMO_DURATION_DAYS'));
    $notif->push($userId, 'Promotion Active', 'Your ' . str_replace('_', ' ', $promoType) . ' promotion is now live.', 'payment', $adId);
    Flash::success('Payment confirmed. Your promotion is now live!');
}
Session::remove('pending_payment_' . $paymentId);
Helpers::redirect('dashboard.php');
