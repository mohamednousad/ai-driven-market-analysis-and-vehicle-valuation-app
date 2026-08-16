<?php
require_once dirname(__DIR__) . '/app/bootstrap.php';
Auth::requireLogin();
Auth::requireCsrf();
$userId = (int)Auth::id();
$type = Helpers::post('type');
$paymentModel = new PaymentModel();
$subModel = new SubscriptionModel();

$scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host = $scheme . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost');

function payhereResponse(int $paymentId, string $itemName, float $amount, string $host): void
{
    $user = Auth::user() ?? [];
    $profile = (new SellerProfileModel())->findByUserId((int)$user['id']) ?? [];
    $checkout = PayHereClient::buildCheckout(
        $paymentId,
        $itemName,
        $amount,
        array_merge($user, array_filter([
            'phone_number' => $profile['phone_number'] ?? null,
            'address' => $profile['address'] ?? null,
            'city' => $profile['city'] ?? null,
        ])),
        $host . Config::baseUrl('payment-success.php?payment_id=' . $paymentId),
        $host . Config::baseUrl('payment-cancel.php?payment_id=' . $paymentId),
        $host . Config::baseUrl('payment-notify.php')
    );
    Helpers::json(['ok' => true, 'payhere' => $checkout]);
}

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
    $paymentId = $paymentModel->create($userId, 'subscription', (float)$plan['price'], PayHereClient::enabled() ? 'payhere' : 'demo', ['plan_id' => (int)$plan['id']]);
    if (PayHereClient::enabled()) {
        payhereResponse($paymentId, 'AutoValue ' . $plan['name'], (float)$plan['price'], $host);
    }
    Helpers::json(['ok' => true, 'redirect' => $host . Config::baseUrl('payment-success.php?payment_id=' . $paymentId . '&demo=1')]);
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
    $paymentId = $paymentModel->create($userId, 'promotion', $price, PayHereClient::enabled() ? 'payhere' : 'demo', ['ad_id' => $adId, 'promo_type' => $promoType]);
    if (PayHereClient::enabled()) {
        payhereResponse($paymentId, 'AutoValue ' . ucfirst(str_replace('_', ' ', $promoType)) . ' Promotion', $price, $host);
    }
    Helpers::json(['ok' => true, 'redirect' => $host . Config::baseUrl('payment-success.php?payment_id=' . $paymentId . '&demo=1')]);
}
Helpers::json(['ok' => false, 'error' => 'Unknown payment type.'], 400);
