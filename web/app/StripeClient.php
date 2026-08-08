<?php
final class StripeClient
{
    public static function enabled(): bool
    {
        return Config::get('STRIPE_SECRET_KEY') !== '';
    }

    public static function createCheckoutSession(string $name, float $amountLkr, string $successUrl, string $cancelUrl, array $metadata): array
    {
        $params = [
            'mode' => 'payment',
            'success_url' => $successUrl . (str_contains($successUrl, '?') ? '&' : '?') . 'session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => $cancelUrl,
            'line_items[0][price_data][currency]' => 'lkr',
            'line_items[0][price_data][product_data][name]' => $name,
            'line_items[0][price_data][unit_amount]' => (int)round($amountLkr * 100),
            'line_items[0][quantity]' => 1,
        ];
        foreach ($metadata as $k => $v) {
            $params['metadata[' . $k . ']'] = (string)$v;
        }
        return self::request('POST', 'https://api.stripe.com/v1/checkout/sessions', $params);
    }

    public static function retrieveSession(string $sessionId): array
    {
        return self::request('GET', 'https://api.stripe.com/v1/checkout/sessions/' . urlencode($sessionId));
    }

    private static function request(string $method, string $url, array $params = []): array
    {
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $method === 'GET' && $params ? $url . '?' . http_build_query($params) : $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_USERPWD => Config::get('STRIPE_SECRET_KEY') . ':',
            CURLOPT_TIMEOUT => 20,
        ]);
        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
        }
        $response = curl_exec($ch);
        $code = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($response === false) {
            return ['ok' => false, 'error' => 'Could not reach Stripe.'];
        }
        $data = json_decode($response, true) ?? [];
        if ($code >= 400) {
            return ['ok' => false, 'error' => $data['error']['message'] ?? 'Stripe request failed.'];
        }
        return ['ok' => true, 'data' => $data];
    }
}
