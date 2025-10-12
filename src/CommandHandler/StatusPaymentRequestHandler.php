<?php

declare(strict_types=1);

namespace Ahmedkhd\SyliusPaymobPlugin\CommandHandler;

use Sylius\Abstraction\StateMachine\StateMachineInterface;
use Ahmedkhd\SyliusPaymobPlugin\Command\StatusPaymentRequest;
use Sylius\Bundle\PaymentBundle\Provider\PaymentRequestProviderInterface;
use Ahmedkhd\SyliusPaymobPlugin\Service\PaymobServiceInterface;
use Sylius\Component\Payment\PaymentRequestTransitions;
use Sylius\Component\Core\Model\PaymentInterface;
use \Exception;

final class StatusPaymentRequestHandler
{
    public function __construct(
        private PaymobServiceInterface $paymobService,
        private PaymentRequestProviderInterface $paymentRequestProvider,
        private StateMachineInterface $stateMachine,
    ) { }

    public function __invoke(StatusPaymentRequest $statusPaymentRequest): void
    {
        $paymentRequest = $this->paymentRequestProvider->provide($statusPaymentRequest);
        $requestData = $statusPaymentRequest->getRequestData();

        $paymentRequest->setPayload($requestData);

        $payment = $paymentRequest->getPayment();
        assert($payment !== null, 'PaymentRequest must have a payment associated.');
        assert($payment instanceof PaymentInterface);

        $gatewayConfig = $paymentRequest->getPayment()->getMethod()?->getGatewayConfig()?->getConfig();
        if ($gatewayConfig === null) {
            throw new Exception(
                'payment method configuration is missing',
            );
        }

        if(
            $requestData['success'] === "true" && 
            isset($requestData["txn_response_code"]) &&
            $requestData["txn_response_code"] === "APPROVED"
        ) {
            if ($this->stateMachine->can(
                $paymentRequest,
                PaymentRequestTransitions::GRAPH,
                PaymentRequestTransitions::TRANSITION_COMPLETE,
            )) {
                $this->stateMachine->apply(
                    $paymentRequest,
                    PaymentRequestTransitions::GRAPH,
                    PaymentRequestTransitions::TRANSITION_COMPLETE,
                );
            }
            // payment should be marked done from the webhook call not by the redirect
        } else {
            if ($this->stateMachine->can(
                $paymentRequest,
                PaymentRequestTransitions::GRAPH,
                PaymentRequestTransitions::TRANSITION_FAIL,
            )) {
                $this->stateMachine->apply(
                    $paymentRequest,
                    PaymentRequestTransitions::GRAPH,
                    PaymentRequestTransitions::TRANSITION_FAIL,
                );
            }
        }
    }
}
