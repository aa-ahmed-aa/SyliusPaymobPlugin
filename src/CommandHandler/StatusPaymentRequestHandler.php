<?php

declare(strict_types=1);

namespace Ahmedkhd\SyliusPaymobPlugin\CommandHandler;

use Sylius\Abstraction\StateMachine\StateMachineInterface;
use Ahmedkhd\SyliusPaymobPlugin\Command\StatusPaymentRequest;
use Sylius\Bundle\PaymentBundle\Provider\PaymentRequestProviderInterface;
use Ahmedkhd\SyliusPaymobPlugin\Service\PaymobServiceInterface;
use Sylius\Component\Payment\PaymentRequestTransitions;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;


#[AsMessageHandler]
final class StatusPaymentRequestHandler
{
    public function __construct(
        private PaymobServiceInterface $paymobService,
        private PaymentRequestProviderInterface $paymentRequestProvider,
        private StateMachineInterface $stateMachine,
    ) {
    }

    public function __invoke(StatusPaymentRequest $statusPaymentRequest): void
    {
        $paymentRequest = $this->paymentRequestProvider->provide($statusPaymentRequest);
        $payment = $paymentRequest->getPayment();
        
        // Get payment details to check status
        $details = $payment->getDetails();
        
        // For now, we'll just check if the payment has been processed
        // In a real implementation, you would call Paymob API to check the actual status
        if (isset($details['status']) && $details['status'] === 'success') {
            $this->stateMachine->apply(
                $paymentRequest,
                PaymentRequestTransitions::GRAPH, 
                PaymentRequestTransitions::TRANSITION_COMPLETE,
            );
        } elseif (isset($details['status']) && $details['status'] === 'failed') {
            $this->stateMachine->apply(
                $paymentRequest,
                PaymentRequestTransitions::GRAPH, 
                PaymentRequestTransitions::TRANSITION_FAIL,
            );
        } else {
            // Payment is still pending
            $this->stateMachine->apply(
                $paymentRequest, 
                PaymentRequestTransitions::GRAPH, 
                PaymentRequestTransitions::TRANSITION_PROCESS,
            );
        }
    }
}
