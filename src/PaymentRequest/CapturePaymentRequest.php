<?php

declare(strict_types=1);

namespace Ahmedkhd\SyliusPaymobPlugin\PaymentRequest;

final class CapturePaymentRequest
{
    public const ACTION = 'capture';

    public function __construct(
        private readonly mixed $payment,
        private readonly array $gatewayConfiguration,
        private readonly array $responseData = []
    ) {
    }

    public function getAction(): string
    {
        return self::ACTION;
    }

    public function getPayment(): mixed
    {
        return $this->payment;
    }

    public function getGatewayConfiguration(): array
    {
        return $this->gatewayConfiguration;
    }

    public function getResponseData(): array
    {
        return $this->responseData;
    }

    public function withResponseData(array $responseData): self
    {
        $new = clone $this;
        $new->responseData = $responseData;

        return $new;
    }
}
