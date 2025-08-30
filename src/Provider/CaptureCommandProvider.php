<?php

declare(strict_types=1);

namespace Ahmedkhd\SyliusPaymobPlugin\Provider;

use Ahmedkhd\SyliusPaymobPlugin\PaymentRequest\CapturePaymentRequest;
use Ahmedkhd\SyliusPaymobPlugin\Command\CapturePaymentCommand;
use Sylius\Bundle\PaymentBundle\CommandProvider\PaymentRequestCommandProviderInterface;
use Sylius\Component\Payment\Model\PaymentRequestInterface;

final class CaptureCommandProvider implements PaymentRequestCommandProviderInterface
{
    public function supports(PaymentRequestInterface $paymentRequest): bool
    {
        return $paymentRequest->getAction() === PaymentRequestInterface::ACTION_CAPTURE;
    }

    public function provide(PaymentRequestInterface $paymentRequest): object
    {
        return new CapturePaymentCommand($paymentRequest->getHash());
    }
}
