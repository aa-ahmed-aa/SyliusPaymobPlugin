<?php

declare(strict_types=1);

namespace Ahmedkhd\SyliusPaymobPlugin\Factory;

final class PaymobGatewayFactory
{
    public function create(mixed $paymentMethod): object
    {
        // This will be implemented to create the gateway configuration
        // For now, we'll return a basic object
        return (object) [
            'gateway_name' => 'paymob',
            'config' => []
        ];
    }

    public function supports(mixed $paymentMethod): bool
    {
        // This will be implemented to check if the payment method is supported
        return true;
    }
}
