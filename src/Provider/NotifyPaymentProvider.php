<?php

declare(strict_types=1);

namespace Ahmedkhd\SyliusPaymobPlugin\Provider;

use Sylius\Bundle\PaymentBundle\Provider\NotifyPaymentProviderInterface;
use Doctrine\ORM\EntityRepository;
use Sylius\Component\Payment\Model\PaymentInterface;
use Sylius\Component\Payment\Model\PaymentMethodInterface;
use Symfony\Component\HttpFoundation\Request;
use Ahmedkhd\SyliusPaymobPlugin\Service\PaymobServiceInterface;

final class NotifyPaymentProvider implements NotifyPaymentProviderInterface
{
    public function __construct(
        private EntityRepository $entityRepository,
    ) {
    }

    public function supports(Request $request, PaymentMethodInterface $paymentMethod): bool
    {
        $requestBody = json_decode($request->getContent(), true);
        
        // Check if this is a GPWebPay payment method and has the required parameters
        return $paymentMethod->getGatewayConfig()?->getFactoryName() === PaymobServiceInterface::GATEWAY_NAME &&
            $requestBody["type"] === PaymobServiceInterface::TRANSACTION_TYPE &&
            $requestBody["obj"]["integration_id"] === (int) $paymentMethod->getGatewayConfig()?->getConfig()["integration_id"];
    }

    public function getPayment(Request $request, PaymentMethodInterface $paymentMethod): PaymentInterface
    {
        $requestBody = json_decode($request->getContent(), true);

        $orderNumber = $requestBody["obj"]["order"]["merchant_order_id"];

        if (!$orderNumber) {
            throw new \InvalidArgumentException('merchant_order_id is required');
        }
        // Find payment by order number and payment method
        $payment = $this->entityRepository->createQueryBuilder('p')
            ->innerJoin('p.order', 'o')
            ->where('p.method = :method')
            ->andWhere('o.number = :orderNumber')
            ->setParameter('method', $paymentMethod)
            ->setParameter('orderNumber', $orderNumber)
            ->orderBy('p.id', 'DESC')
            ->getQuery()
            ->getOneOrNullResult()
            ;

        if ($payment) {
            assert($payment instanceof PaymentInterface);

            return $payment;
        }

        throw new \RuntimeException(sprintf('Payment with order number "%s" not found', $orderNumber));
    }
}