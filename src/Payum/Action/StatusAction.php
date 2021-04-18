<?php

declare(strict_types=1);

namespace Ahmedkhd\SyliusPaymobPlugin\Payum\Action;

use Payum\Core\Action\ActionInterface;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Request\GetStatusInterface;
use Sylius\Component\Core\Model\PaymentInterface as SyliusPaymentInterface;
use Sylius\Component\Core\OrderPaymentStates;

final class StatusAction implements ActionInterface
{
    public function execute($request): void
    {
        RequestNotSupportedException::assertSupports($this, $request);

        /** @var SyliusPaymentInterface $payment */
        $payment = $request->getFirstModel();
        switch ($payment->getState()){
            case SyliusPaymentInterface::STATE_COMPLETED:
                $request->markCaptured();
                $payment->getOrder()->setPaymentState(OrderPaymentStates::STATE_PAID);
                break;
            case SyliusPaymentInterface::STATE_CANCELLED:
                $request->markCanceled();
                $payment->getOrder()->setPaymentState(OrderPaymentStates::STATE_CANCELLED);
                break;
            default:
                $payment->getOrder()->setPaymentState(OrderPaymentStates::STATE_AWAITING_PAYMENT);
                $request->markFailed();
        }
    }

    public function supports($request): bool
    {
        return
            $request instanceof GetStatusInterface &&
            $request->getFirstModel() instanceof SyliusPaymentInterface
            ;
    }
}
