<?php

declare(strict_types=1);

namespace Ahmedkhd\SyliusPaymobPlugin\CommandHandler;

use Ahmedkhd\SyliusPaymobPlugin\Command\CapturePaymentCommand;
use Sylius\Abstraction\StateMachine\StateMachineInterface;
use Sylius\Bundle\PaymentBundle\Provider\PaymentRequestProviderInterface;
use Sylius\Component\Payment\PaymentRequestTransitions;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class CapturePaymentCommandHandler
{
    public function __construct(
        private PaymentRequestProviderInterface $paymentRequestProvider,
        private StateMachineInterface $stateMachine,
    ) {}

    public function __invoke(CapturePaymentCommand $capturePaymentCommand): void
    {
        // Retrieve the current PaymentRequest based on the hash provided in the CapturePaymentCommand
        $paymentRequest = $this->paymentRequestProvider->provide($capturePaymentCommand);

        // Custom capture logic for the payment provider would go here.
        // Example: communicating with the payment gateway API to capture funds.

        // Mark the PaymentRequest as complete|process|fail|cancel.
        $this->stateMachine->apply(
            $paymentRequest,
            PaymentRequestTransitions::GRAPH,
            PaymentRequestTransitions::TRANSITION_COMPLETE
        );
    }
}
