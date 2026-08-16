<?php
final class PaymentFulfillment
{
    public static function complete(array $payment, string $transactionId): array
    {
        $paymentId = (int)$payment['id'];
        $userId = (int)$payment['user_id'];
        $meta = json_decode((string)($payment['meta'] ?? '{}'), true) ?: [];
        $paymentModel = new PaymentModel();
        $notif = new NotificationModel();

        if ($payment['status'] === 'completed') {
            return ['ok' => true, 'message' => 'This payment was already confirmed.'];
        }

        if ($payment['category'] === 'subscription') {
            $plan = (new SubscriptionModel())->planById((int)($meta['plan_id'] ?? 0));
            if (!$plan) {
                $paymentModel->fail($paymentId);
                return ['ok' => false, 'message' => 'The plan linked to this payment no longer exists.'];
            }
            $subId = (new SubscriptionModel())->activate($userId, $plan);
            $paymentModel->complete($paymentId, $transactionId, $subId);
            $notif->push($userId, 'Plan Activated', 'Your ' . $plan['name'] . ' is now active. Enjoy your benefits!', 'payment');
            return ['ok' => true, 'message' => 'Payment confirmed. Your ' . $plan['name'] . ' is now active!'];
        }

        if ($payment['category'] === 'promotion') {
            $adId = (int)($meta['ad_id'] ?? 0);
            $promoType = (string)($meta['promo_type'] ?? '');
            $ad = $adId ? (new AdModel())->findFull($adId) : null;
            if (!$ad || !in_array($promoType, ['featured', 'top_listing', 'highlight'], true)) {
                $paymentModel->fail($paymentId);
                return ['ok' => false, 'message' => 'The ad linked to this payment could not be found.'];
            }
            $paymentModel->complete($paymentId, $transactionId);
            (new PromotionModel())->create($adId, $paymentId, $promoType, (float)$payment['amount'], (int)Config::get('PROMO_DURATION_DAYS'));
            $notif->push($userId, 'Promotion Active', 'Your ' . str_replace('_', ' ', $promoType) . ' promotion is now live.', 'payment', $adId);
            return ['ok' => true, 'message' => 'Payment confirmed. Your promotion is now live!'];
        }

        $paymentModel->fail($paymentId);
        return ['ok' => false, 'message' => 'Unknown payment category.'];
    }
}
