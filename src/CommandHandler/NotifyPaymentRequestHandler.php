<?php

declare(strict_types=1);

namespace Ahmedkhd\SyliusPaymobPlugin\CommandHandler;

use Sylius\Abstraction\StateMachine\StateMachineInterface;
use Ahmedkhd\SyliusPaymobPlugin\Command\NotifyPaymentRequest;
use Sylius\Bundle\PaymentBundle\Provider\PaymentRequestProviderInterface;
use Ahmedkhd\SyliusPaymobPlugin\Service\PaymobServiceInterface;
use Sylius\Component\Payment\PaymentRequestTransitions;
use Sylius\Component\Payment\PaymentTransitions;

final readonly class NotifyPaymentRequestHandler
{
    public function __construct(
        private PaymentRequestProviderInterface $paymentRequestProvider,
        private StateMachineInterface $stateMachine,
    ) {}

    public function __invoke(NotifyPaymentRequest $notifyPaymentRequest): void
    {
        $paymentRequest = $this->paymentRequestProvider->provide($notifyPaymentRequest);
        $requestData = $notifyPaymentRequest->getRequestData();

        $paymobObjectType = $requestData["type"] ?? null;
        $paymobTransaction = $requestData["obj"] ?? null;

        $payment = $paymentRequest->getPayment();

        //success payment
        if(
            !empty($paymobObjectType) &&
            !empty($paymobTransaction) &&
            $paymobObjectType === PaymobServiceInterface::TRANSACTION_TYPE &&
            isset($paymobTransaction["order"]["paid_amount_cents"]) &&
            isset($paymobTransaction["order"]["merchant_order_id"])
        ) {
            $orderAmount = $paymobTransaction["order"]["paid_amount_cents"];
            $amount = $payment->getAmount();
            if($orderAmount === $amount) {
                $payment->setDetails(['status'=> PaymentRequestTransitions::TRANSITION_COMPLETE, 'message' => "amount: {$amount}, currency: {$payment->getOrder()->getCurrencyCode()}"]);
                $paymentRequest->setPayload($requestData);
                $paymentRequest->setResponseData(['status'=> PaymentRequestTransitions::TRANSITION_COMPLETE, 'message' => "amount: {$amount}, currency: {$payment->getOrder()->getCurrencyCode()}"]);

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

                if ($this->stateMachine->can(
                    $payment,
                    PaymentTransitions::GRAPH,
                    PaymentTransitions::TRANSITION_COMPLETE,
                )) {
                    $this->stateMachine->apply(
                        $payment,
                        PaymentTransitions::GRAPH,
                        PaymentTransitions::TRANSITION_COMPLETE,
                    );
                }
            }
        } else if ($paymobTransaction['success'] === false) {
            $orderAmount = $paymobTransaction["order"]["paid_amount_cents"];

            $payment->setDetails(['status'=> PaymentRequestTransitions::TRANSITION_FAIL, 'message' => "amount: {$orderAmount}, currency: {$payment->getOrder()->getCurrencyCode()}"]);
            $paymentRequest->setPayload($requestData);
            $paymentRequest->setResponseData(['status'=> PaymentRequestTransitions::TRANSITION_FAIL, 'message' => "amount: {$orderAmount}, currency: {$payment->getOrder()->getCurrencyCode()}"]);

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