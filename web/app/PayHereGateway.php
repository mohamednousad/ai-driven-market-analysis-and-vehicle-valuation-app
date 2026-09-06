<?php
final class PayHereGateway
{
    public function buildCheckout(int $paymentId, string $itemName, float $amountLkr, array $user, string $returnUrl, string $cancelUrl, string $notifyUrl): array
    {
        return PayHereClient::buildCheckout($paymentId, $itemName, $amountLkr, $user, $returnUrl, $cancelUrl, $notifyUrl);
    }

    public function verifyNotify(array $post): bool
    {
        return PayHereClient::verifyNotify($post);
    }

    public function enabled(): bool
    {
        return PayHereClient::enabled();
    }

    public function paymentIdFromOrder(string $orderId): int
    {
        return PayHereClient::paymentIdFromOrder($orderId);
    }

    public function orderId(int $paymentId): string
    {
        return PayHereClient::orderId($paymentId);
    }
}