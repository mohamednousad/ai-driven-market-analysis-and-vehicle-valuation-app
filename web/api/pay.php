<?php
require_once dirname(__DIR__) . '/app/bootstrap.php';
Auth::requireLogin();
Auth::requireCsrf();
$userId = (int)Auth::id();
$type = Helpers::post('type');
$paymentModel = new PaymentModel();
$subModel = new SubscriptionModel();

if ($type === 'subscription') {
    $plan = $subModel->planById((int)Helpers::post('plan_id'));
    if (!$plan) {
        Helpers::json(['ok' => false, 'error' => 'Plan not found.'], 404);
    }
    $active = $subModel->activeForUser($userId);
    if ($active && (int)$active['plan_id'] === (int)$plan['id']) {
        Helpers::json(['ok' => false, 'error' => 'You already have this plan active.'], 422);
    }
    if (!(new SellerProfileModel())->findByUserId($userId)) {
        (new SellerProfileModel())->createOrUpdate($userId, ['phone_number' => null, 'date_of_birth' => null, 'gender' => null, 'bio' => null, 'address' => null, 'city' => null, 'district' => null, 'province' => null, 'postal_code' => null]);
    }
    $paymentId = $paymentModel->create($userId, 'subscription', (float)$plan['price'], StripeClient::enabled() ? 'card' : 'demo');
    Session::set('pending_payment_' . $paymentId, json_encode(['plan_id' => (int)$plan['id']]));
    $successUrl = 'http://' . $_SERVER['HTTP_HOST'] . Config::baseUrl('payment-success.php?payment_id=' . $paymentId);
    $cancelUrl = 'http://' . $_SERVER['HTTP_HOST'] . Config::baseUrl('payment-cancel.php?payment_id=' . $paymentId);
    if (StripeClient::enabled()) {
        $session = StripeClient::createCheckoutSession('AutoValue ' . $plan['name'], (float)$plan['price'], $successUrl, $cancelUrl, ['payment_id' => $paymentId]);
        if (!$session['ok']) {
            $paymentModel->fail($paymentId);
            Helpers::json(['ok' => false, 'error' => $session['error']], 502);
        }
        Helpers::json(['ok' => true, 'redirect' => $session['data']['url']]);
    }
    Helpers::json(['ok' => true, 'redirect' => $successUrl . '&demo=1']);
}

if ($type === 'promotion') {
    $adId = (int)Helpers::post('ad_id');
    $promoType = Helpers::post('promo_type');
    $mode = Helpers::post('mode');
    if (!in_array($promoType, ['featured', 'top_listing', 'highlight'], true)) {
        Helpers::json(['ok' => false, 'error' => 'Unknown promotion type.'], 422);
    }
    $ad = (new AdModel())->findFull($adId);
    if (!$ad || (int)$ad['seller_id'] !== $userId || $ad['status'] !== 'approved') {
        Helpers::json(['ok' => false, 'error' => 'Only your own approved ads can be promoted.'], 422);
    }
    if ((new PromotionModel())->activeForAd($adId)) {
        Helpers::json(['ok' => false, 'error' => 'This ad already has an active promotion.'], 422);
    }
    $settingRow = Database::getInstance()->fetchOne('SELECT `value` FROM settings WHERE `key` = ?', ['promo_price_' . $promoType]);
    $price = (float)($settingRow['value'] ?? 2000);
    if ($mode === 'free') {
        $active = $subModel->activeForUser($userId);
        if (!$active || !$subModel->consumeFreePromotion((int)$active['id'])) {
            Helpers::json(['ok' => false, 'error' => 'No free promotions remaining on your plan.'], 422);
        }
        (new PromotionModel())->create($adId, null, $promoType, 0, (int)Config::get('PROMO_DURATION_DAYS'));
        (new NotificationModel())->push($userId, 'Promotion Active', 'Your free ' . str_replace('_', ' ', $promoType) . ' promotion is now live.', 'payment', $adId);
        Helpers::json(['ok' => true, 'message' => 'Free promotion applied. Your ad is now boosted!']);
    }
    $paymentId = $paymentModel->create($userId, 'promotion', $price, StripeClient::enabled() ? 'card' : 'demo');
    Session::set('pending_payment_' . $paymentId, json_encode(['ad_id' => $adId, 'promo_type' => $promoType]));
    $successUrl = 'http://' . $_SERVER['HTTP_HOST'] . Config::baseUrl('payment-success.php?payment_id=' . $paymentId);
    $cancelUrl = 'http://' . $_SERVER['HTTP_HOST'] . Config::baseUrl('payment-cancel.php?payment_id=' . $paymentId);
    if (StripeClient::enabled()) {
        $session = StripeClient::createCheckoutSession('AutoValue ' . ucfirst(str_replace('_', ' ', $promoType)) . ' Promotion', $price, $successUrl, $cancelUrl, ['payment_id' => $paymentId]);
        if (!$session['ok']) {
            $paymentModel->fail($paymentId);
            Helpers::json(['ok' => false, 'error' => $session['error']], 502);
        }
        Helpers::json(['ok' => true, 'redirect' => $session['data']['url']]);
    }
    Helpers::json(['ok' => true, 'redirect' => $successUrl . '&demo=1']);
}
Helpers::json(['ok' => false, 'error' => 'Unknown payment type.'], 400);
