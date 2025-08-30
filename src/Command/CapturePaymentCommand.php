<?php

declare(strict_types=1);

namespace Ahmedkhd\SyliusPaymobPlugin\Command;

final class CapturePaymentCommand
{
    public function __construct(
        private readonly mixed $payment,
        private readonly array $gatewayConfiguration
    ) {
    }

    public function getPayment(): mixed
    {
        return $this->payment;
    }

    public function getGatewayConfiguration(): array
    {
        return $this->gatewayConfiguration;
    }
}
