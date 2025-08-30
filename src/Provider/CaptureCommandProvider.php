<?php

declare(strict_types=1);

namespace Ahmedkhd\SyliusPaymobPlugin\Provider;

use Ahmedkhd\SyliusPaymobPlugin\PaymentRequest\CapturePaymentRequest;
use Ahmedkhd\SyliusPaymobPlugin\Command\CapturePaymentCommand;

final class CaptureCommandProvider
{
    public function supports(mixed $paymentRequest): bool
    {
        return $paymentRequest instanceof CapturePaymentRequest;
    }

    public function getCommand(mixed $paymentRequest): object
    {
        if (!$paymentRequest instanceof CapturePaymentRequest) {
            throw new \InvalidArgumentException('Expected CapturePaymentRequest');
        }

        return new CapturePaymentCommand(
            $paymentRequest->getPayment(),
            $paymentRequest->getGatewayConfiguration()
        );
    }
}
