<?php
final class PaymentGatewayFactory
{
    public static function create(string $gateway = 'payhere'): PayHereGateway
    {
        switch ($gateway) {
            case 'payhere':
                return new PayHereGateway();
            default:
                throw new InvalidArgumentException("Unsupported gateway: {$gateway}");
        }
    }
}