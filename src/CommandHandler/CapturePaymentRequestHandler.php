<?php

declare(strict_types=1);

namespace Ahmedkhd\SyliusPaymobPlugin\CommandHandler;

use GuzzleHttp\Exception\RequestException;

use Sylius\Abstraction\StateMachine\StateMachineInterface;
use Ahmedkhd\SyliusPaymobPlugin\Command\CapturePaymentRequest;
use Sylius\Bundle\PaymentBundle\Provider\PaymentRequestProviderInterface;
use Ahmedkhd\SyliusPaymobPlugin\Service\PaymobServiceInterface;
use Ahmedkhd\SyliusPaymobPlugin\Model\Paymob;
use Sylius\Component\Payment\PaymentRequestTransitions;
use Sylius\Component\Payment\Repository\PaymentRequestRepositoryInterface;
final readonly class CapturePaymentRequestHandler
{
    public function __construct(
        private PaymobServiceInterface $paymobService,
        private PaymentRequestProviderInterface $paymentRequestProvider,
        private StateMachineInterface $stateMachine,
        private PaymentRequestRepositoryInterface $paymentRequestRepository,
    ) { }

    public function __invoke(CapturePaymentRequest $capturePaymentRequest): void
    {
        try {
            $paymentRequest = $this->paymentRequestProvider->provide($capturePaymentRequest);

            $payload = $paymentRequest->getPayload();
            $paymobOrderId = $payload['paymobOrderId'] ?? null;

            // Set the Paymob configuration from the payment method
            $paymentMethod = $paymentRequest->getMethod();
            $gatewayConfig = $paymentMethod->getGatewayConfig();
            
            $paymobConfig = new Paymob(
                $gatewayConfig->getConfig()['api_key'],
                $gatewayConfig->getConfig()['hmac_security'],
                $gatewayConfig->getConfig()['merchant_id'],
                $gatewayConfig->getConfig()['iframe_id'],
                $gatewayConfig->getConfig()['integration_id']
            );

            $this->paymobService->setPaymobConfig($paymobConfig);

            $authToken = $this->paymobService->authenticate();
            if(empty($paymobOrderId)) {
                $paymobOrderId = $this->paymobService->createOrderId($paymentRequest, $authToken);
            }
            $paymentToken = $this->paymobService->getPaymentKey($paymentRequest, $authToken, $paymobOrderId);

            $paymentMethodConfigs = $this->paymobService->getPaymobConfig();

            $iframeURL = "https://accept.paymobsolutions.com/api/acceptance/iframes/{$paymentMethodConfigs->getIframe()}?payment_token={$paymentToken}";
            
            $paymentRequest->setPayload([
                "iframeUrl" => $iframeURL,
                "paymobOrderId" => $paymobOrderId,
                "currentOrderId" => $paymentRequest->getPayment()->getOrder()->getId()
            ]);

            if ($this->stateMachine->can(
                $paymentRequest,
                PaymentRequestTransitions::GRAPH,
                PaymentRequestTransitions::TRANSITION_PROCESS,
            )) {
                $this->stateMachine->apply(
                    $paymentRequest,
                    PaymentRequestTransitions::GRAPH,
                    PaymentRequestTransitions::TRANSITION_PROCESS,
                );
            }
        } catch (RequestException $exception) {
            $paymentRequest->setResponseData(['status'=> "failed", "message" => $exception->getMessage()]);
            $this->stateMachine->apply(
                $paymentRequest, 
                PaymentRequestTransitions::GRAPH, 
                PaymentRequestTransitions::TRANSITION_FAIL
            );
        }
    }
}