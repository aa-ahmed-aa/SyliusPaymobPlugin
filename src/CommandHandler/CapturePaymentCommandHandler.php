<?php

declare(strict_types=1);

namespace Ahmedkhd\SyliusPaymobPlugin\CommandHandler;

use Ahmedkhd\SyliusPaymobPlugin\Command\CapturePaymentCommand;

final class CapturePaymentCommandHandler
{
    public function __invoke(CapturePaymentCommand $command): void
    {
        $payment = $command->getPayment();
        $gatewayConfig = $command->getGatewayConfiguration();

        try {
            // This will be implemented with the actual Paymob API calls
            // For now, we'll just set the payment as pending
            $payment->setDetails(['status' => 'pending', 'gateway' => 'paymob']);
            
        } catch (\Exception $exception) {
            $payment->setDetails(['status' => 'failed', 'message' => $exception->getMessage()]);
            $payment->setState('failed');
        }
    }
}
