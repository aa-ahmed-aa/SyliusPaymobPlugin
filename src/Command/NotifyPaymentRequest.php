<?php

declare(strict_types=1);

namespace Ahmedkhd\SyliusPaymobPlugin\Command;

use Sylius\Bundle\PaymentBundle\Command\PaymentRequestHashAwareInterface;
use Sylius\Bundle\PaymentBundle\Command\PaymentRequestHashAwareTrait;

class NotifyPaymentRequest implements PaymentRequestHashAwareInterface
{
    use PaymentRequestHashAwareTrait;

    public function __construct(
        ?string $hash,
        private readonly array $requestData = [],
    ) {
        $this->hash = $hash;
    }

    public function getRequestData(): array
    {
        return $this->requestData;
    }
}