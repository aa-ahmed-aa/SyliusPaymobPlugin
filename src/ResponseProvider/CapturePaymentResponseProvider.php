<?php

declare(strict_types=1);

namespace Ahmedkhd\SyliusPaymobPlugin\ResponseProvider;

use Sylius\Bundle\PaymentBundle\Provider\HttpResponseProviderInterface;
use Sylius\Bundle\ResourceBundle\Controller\RequestConfiguration;
use Sylius\Component\Payment\Model\PaymentRequestInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;


final readonly class CapturePaymentResponseProvider  implements HttpResponseProviderInterface
{
    public function __construct(
        private RouterInterface $router,
    ) {
    }

    public function supports(RequestConfiguration $requestConfiguration, PaymentRequestInterface $paymentRequest): bool
    {
        return $paymentRequest->getAction() === PaymentRequestInterface::ACTION_CAPTURE;
    }

    public function getResponse(
        RequestConfiguration $requestConfiguration,
        PaymentRequestInterface $paymentRequest,
    ): Response {
        $requestData = $paymentRequest->getPayload();

        return new RedirectResponse(
            $requestData['iframeUrl'],
            Response::HTTP_SEE_OTHER,
        );
    }
}