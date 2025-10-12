<?php

declare(strict_types=1);

namespace Ahmedkhd\SyliusPaymobPlugin\CommandProvider;

use Sylius\Component\Payment\Model\PaymentRequestInterface;
use Ahmedkhd\SyliusPaymobPlugin\Command\StatusPaymentRequest;
use Sylius\Bundle\PaymentBundle\CommandProvider\PaymentRequestCommandProviderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
final class StatusPaymentRequestCommandProvider implements PaymentRequestCommandProviderInterface
{
    public function __construct(
        private RequestStack $requestStack,
    ) {
    }

    public function supports(PaymentRequestInterface $paymentRequest): bool
    {
        return $paymentRequest->getAction() === PaymentRequestInterface::ACTION_STATUS;
    }

    public function provide(PaymentRequestInterface $paymentRequest): object
    {
        $request = $this->requestStack->getCurrentRequest();
        $responseData = [];

        if ($request instanceof Request) {
            // Paymob sends data via GET parameters
            $requestData = $request->query->all() ?? [];
        }

        return new StatusPaymentRequest($paymentRequest->getId(), $requestData);
    }
}
