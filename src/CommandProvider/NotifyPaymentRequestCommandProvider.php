<?php

declare(strict_types=1);

namespace Ahmedkhd\SyliusPaymobPlugin\CommandProvider;

use Sylius\Component\Payment\Model\PaymentRequestInterface;
use Ahmedkhd\SyliusPaymobPlugin\Command\NotifyPaymentRequest;
use Sylius\Bundle\PaymentBundle\CommandProvider\PaymentRequestCommandProviderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

final readonly class NotifyPaymentRequestCommandProvider implements PaymentRequestCommandProviderInterface
{
    public function __construct(
        private RequestStack $requestStack,
    ) {
    }

    public function supports(PaymentRequestInterface $paymentRequest): bool
    {
        return $paymentRequest->getAction() === PaymentRequestInterface::ACTION_NOTIFY;
    }

    public function provide(PaymentRequestInterface $paymentRequest): object
    {
        $request = $this->requestStack->getCurrentRequest();
        $requestData = [];

        if ($request instanceof Request) {
            $requestData = $request->getContent();
        }
        
        return new NotifyPaymentRequest($paymentRequest->getId(), json_decode($requestData, true));
    }
}