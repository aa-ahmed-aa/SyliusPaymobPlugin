<?php

declare(strict_types=1);

namespace Ahmedkhd\SyliusPaymobPlugin\CommandProvider;

use Sylius\Component\Payment\Model\PaymentRequestInterface;
use Ahmedkhd\SyliusPaymobPlugin\Command\StatusPaymentRequest;
use Sylius\Bundle\PaymentBundle\CommandProvider\PaymentRequestCommandProviderInterface;

final class StatusPaymentRequestCommandProvider implements PaymentRequestCommandProviderInterface
{
    public function supports(PaymentRequestInterface $paymentRequest): bool
    {
       return true;
    }

    public function provide(PaymentRequestInterface $paymentRequest): StatusPaymentRequest
    {
        $hash = $paymentRequest->getHash();
        return new StatusPaymentRequest($hash instanceof \Stringable ? (string) $hash : $hash);
    }
}
