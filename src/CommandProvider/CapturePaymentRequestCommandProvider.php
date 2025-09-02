<?php

declare(strict_types=1);

namespace Ahmedkhd\SyliusPaymobPlugin\CommandProvider;


use Sylius\Component\Payment\Model\PaymentRequestInterface;
use Ahmedkhd\SyliusPaymobPlugin\Command\CapturePaymentRequest;
use Sylius\Bundle\PaymentBundle\CommandProvider\PaymentRequestCommandProviderInterface;


final readonly class CapturePaymentRequestCommandProvider implements PaymentRequestCommandProviderInterface
{
    public function supports(PaymentRequestInterface $paymentRequest): bool
    {
        return true;
    }

    public function provide(PaymentRequestInterface $paymentRequest): CapturePaymentRequest
    {
        $hash = $paymentRequest->getHash();
        return new CapturePaymentRequest($hash instanceof \Stringable ? (string) $hash : $hash);
    }
}