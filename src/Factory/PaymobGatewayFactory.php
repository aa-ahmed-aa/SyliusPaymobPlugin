<?php

declare(strict_types=1);

namespace Ahmedkhd\SyliusPaymobPlugin\Factory;

use Sylius\Component\Core\Model\PaymentMethodInterface;

final class PaymobGatewayFactory
{
    public function create(PaymentMethodInterface $paymentMethod): array
    {
        return [
            'gateway_name' => 'paymob',
            'factory_name' => 'paymob',
            'config' => [
                'api_key' => '',
                'hamc_security' => '',
                'merchant_id' => '',
                'iframe' => '',
                'integration_id' => ''
            ]
        ];
    }

    public function supports(PaymentMethodInterface $paymentMethod): bool
    {
        return $paymentMethod->getGatewayConfig() !== null && 
               $paymentMethod->getGatewayConfig()->getFactoryName() === 'paymob';
    }
}
