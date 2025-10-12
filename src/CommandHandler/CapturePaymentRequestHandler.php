<?php

declare(strict_types=1);

namespace Ahmedkhd\SyliusPaymobPlugin\CommandHandler;

use GuzzleHttp\Exception\RequestException;

use Sylius\Abstraction\StateMachine\StateMachineInterface;
use Ahmedkhd\SyliusPaymobPlugin\Command\CapturePaymentRequest;
use Sylius\Bundle\PaymentBundle\Provider\PaymentRequestProviderInterface;
use Ahmedkhd\SyliusPaymobPlugin\Service\PaymobServiceInterface;
use Sylius\Bundle\CoreBundle\Doctrine\ORM\OrderRepository;
use Sylius\Bundle\CoreBundle\Doctrine\ORM\CustomerRepository;
use Sylius\Component\Payment\PaymentRequestTransitions;
use Sylius\Component\Payment\Repository\PaymentRequestRepositoryInterface;

final readonly class CapturePaymentRequestHandler
{
    public function __construct(
        private PaymobServiceInterface $paymobService,
        private PaymentRequestProviderInterface $paymentRequestProvider,
        private StateMachineInterface $stateMachine,
        private PaymentRequestRepositoryInterface $paymentRequestRepository,
        private OrderRepository $orderRepository,
        private CustomerRepository $customerRepository,
    ) { }

    public function __invoke(CapturePaymentRequest $capturePaymentRequest): void
    {
        try {
            $paymentRequest = $this->paymentRequestProvider->provide($capturePaymentRequest);

            $paymentConfig = $paymentRequest->getMethod()->getGatewayConfig()->getConfig();
            $payload = $paymentRequest->getPayload();
            $paymobOrderId = $payload['paymobOrderId'] ?? null;

            $authToken = $this->paymobService->authenticate($paymentRequest);
            if(empty($paymobOrderId)) {
                $paymobOrderId = $this->paymobService->createOrderId($paymentRequest, $authToken);
            }
            $paymentToken = $this->paymobService->getPaymentKey($paymentRequest, $authToken, $paymobOrderId);

            $iframeURL = "https://accept.paymobsolutions.com/api/acceptance/iframes/{$paymentConfig["iframe_id"]}?payment_token={$paymentToken}";
            
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