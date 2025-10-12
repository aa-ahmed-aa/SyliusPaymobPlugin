<?php

declare(strict_types=1);

namespace Ahmedkhd\SyliusPaymobPlugin\ResponseProvider;

use Sylius\Bundle\PaymentBundle\Provider\HttpResponseProviderInterface;
use Sylius\Bundle\ResourceBundle\Controller\RequestConfiguration;
use Sylius\Component\Payment\Model\PaymentRequestInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\RouterInterface;
use Sylius\Component\Core\Repository\OrderRepositoryInterface;

final readonly class StatusHttpResponseProvider implements HttpResponseProviderInterface
{

    public function __construct(
        private RouterInterface $router,
        private OrderRepositoryInterface $orderRepository,
    ) {
    }

    public function supports(
        RequestConfiguration $requestConfiguration,
        PaymentRequestInterface $paymentRequest,
    ): bool {
        return $paymentRequest->getAction() === PaymentRequestInterface::ACTION_STATUS;
    }

    public function getResponse(
        RequestConfiguration $requestConfiguration,
        PaymentRequestInterface $paymentRequest
    ): RedirectResponse {
        if ($paymentRequest->getState() === PaymentRequestInterface::STATE_COMPLETED) {
            return new RedirectResponse(
                $this->router->generate("sylius_shop_order_thank_you"),
                RedirectResponse::HTTP_SEE_OTHER,
            );
        } else {
            $merchantOrderId = $paymentRequest->getPayload()['merchant_order_id'];
            $order = $this->orderRepository->findOneBy(["number" => $merchantOrderId]);
            return new RedirectResponse(
                $this->router->generate("sylius_shop_order_show",["tokenValue" => $order->getTokenValue()]),
                RedirectResponse::HTTP_SEE_OTHER,
            );
        }
    }
}