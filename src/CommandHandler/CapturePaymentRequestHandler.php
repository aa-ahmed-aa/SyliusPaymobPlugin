<?php

declare(strict_types=1);

namespace Ahmedkhd\SyliusPaymobPlugin\CommandHandler;

use GuzzleHttp\Exception\RequestException;
use Sylius\Abstraction\StateMachine\StateMachineInterface;
use Ahmedkhd\SyliusPaymobPlugin\Command\CapturePaymentRequest;
use Sylius\Bundle\PaymentBundle\Provider\PaymentRequestProviderInterface;
use Ahmedkhd\SyliusPaymobPlugin\Service\PaymobServiceInterface;
use Ahmedkhd\SyliusPaymobPlugin\Model\Paymob;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Sylius\Component\Payment\PaymentRequestTransitions;


#[AsMessageHandler]
final readonly class CapturePaymentRequestHandler
{
    public function __construct(
        private PaymobServiceInterface $paymobService,
        private PaymentRequestProviderInterface $paymentRequestProvider,
        private StateMachineInterface $stateMachine,
    ) { }

    public function __invoke(CapturePaymentRequest $capturePaymentRequest): void
    {
        $paymentRequest = $this->paymentRequestProvider->provide($capturePaymentRequest);
        
        // Get the payment from the payment request
        $payment = $paymentRequest->getPayment();
        // Set the Paymob configuration from the payment method
        $paymentMethod = $payment->getMethod();
        $gatewayConfig = $paymentMethod->getGatewayConfig();
        
        $paymobConfig = new Paymob(
            $gatewayConfig->getConfig()['api_key'],
            $gatewayConfig->getConfig()['hmac_security'],
            $gatewayConfig->getConfig()['merchant_id'],
            $gatewayConfig->getConfig()['iframe_id'],
            $gatewayConfig->getConfig()['integration_id']
        );


        $this->paymobService->setPaymobConfig($paymobConfig);

        try {
            $authToken = $this->paymobService->authenticate();
            $orderId = $this->paymobService->createOrderId($payment, $authToken);
            $paymentToken = $this->paymobService->getPaymentKey($payment, $authToken, strval($orderId));

            $paymentMethodConfigs = $this->paymobService->getPaymobConfig();

            $iframeURL = "https://accept.paymobsolutions.com/api/acceptance/iframes/{$paymentMethodConfigs->getIframe()}?payment_token={$paymentToken}";
            
            // Store the iframe URL in payment details
            $payment->setDetails(['iframe_url' => $iframeURL]);
            
            // Update payment state to pending
            $this->stateMachine->apply(
                $paymentRequest,
                PaymentRequestTransitions::GRAPH,
                PaymentRequestTransitions::TRANSITION_PROCESS,
            );

            // Redirect to Paymob iframe
            $this->paymobService->doPayment($iframeURL);
        } catch (RequestException $exception) {
            dd($exception);
            $payment->setDetails(['status'=> "failed", "message" => $exception->getMessage()]);
            $this->stateMachine->apply(
                $paymentRequest, 
                PaymentRequestTransitions::GRAPH, 
                PaymentRequestTransitions::TRANSITION_FAIL
            );
            return;
        }
    }
}