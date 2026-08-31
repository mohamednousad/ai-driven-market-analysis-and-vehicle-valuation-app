<?php
final class PayHereClient
{
    public static function enabled(): bool
    {
        return Config::get('PAYHERE_MERCHANT_ID') !== '' && Config::get('PAYHERE_MERCHANT_SECRET') !== '';
    }

    public static function checkoutUrl(): string
    {
        return Config::get('PAYHERE_SANDBOX')
            ? 'https://sandbox.payhere.lk/pay/checkout'
            : 'https://www.payhere.lk/pay/checkout';
    }

    public static function orderId(int $paymentId): string
    {
        return 'AV' . str_pad((string)$paymentId, 6, '0', STR_PAD_LEFT);
    }

    public static function paymentIdFromOrder(string $orderId): int
    {
        return (int)ltrim(substr($orderId, 2), '0');
    }

    public static function buildCheckout(int $paymentId, string $itemName, float $amountLkr, array $user, string $returnUrl, string $cancelUrl, string $notifyUrl): array
    {
        $merchantId = (string)Config::get('PAYHERE_MERCHANT_ID');
        $secret = (string)Config::get('PAYHERE_MERCHANT_SECRET');
        $orderId = self::orderId($paymentId);
        $amount = number_format($amountLkr, 2, '.', '');
        $currency = (string)Config::get('CURRENCY');
        $hash = strtoupper(md5($merchantId . $orderId . $amount . $currency . strtoupper(md5($secret))));
        $nameParts = preg_split('/\s+/', trim((string)($user['username'] ?? 'AutoValue User'))) ?: ['AutoValue'];
        return [
            'action' => self::checkoutUrl(),
            'fields' => [
                'merchant_id' => $merchantId,
                'return_url' => $returnUrl,
                'cancel_url' => $cancelUrl,
                'notify_url' => $notifyUrl,
                'order_id' => $orderId,
                'items' => $itemName,
                'currency' => $currency,
                'amount' => $amount,
                'first_name' => $nameParts[0],
                'last_name' => $nameParts[1] ?? 'Seller',
                'email' => (string)($user['email'] ?? 'noreply@autovalue.lk'),
                'phone' => (string)($user['phone_number'] ?? '0770000000'),
                'address' => (string)($user['address'] ?? 'Colombo'),
                'city' => (string)($user['city'] ?? 'Colombo'),
                'country' => 'Sri Lanka',
                'hash' => $hash,
            ],
        ];
    }

    public static function verifyNotify(array $post): bool
    {
        $merchantId = (string)Config::get('PAYHERE_MERCHANT_ID');
        $secret = (string)Config::get('PAYHERE_MERCHANT_SECRET');
        $required = ['merchant_id', 'order_id', 'payhere_amount', 'payhere_currency', 'status_code', 'md5sig'];
        foreach ($required as $key) {
            if (!isset($post[$key])) {
                return false;
            }
        }
        if ($post['merchant_id'] !== $merchantId) {
            return false;
        }
        $localSig = strtoupper(md5(
            $post['merchant_id']
            . $post['order_id']
            . $post['payhere_amount']
            . $post['payhere_currency']
            . $post['status_code']
            . strtoupper(md5($secret))
        ));
        return hash_equals($localSig, strtoupper((string)$post['md5sig']));
    }
}
