<?php


namespace Ahmedkhd\SyliusPaymobPlugin\Services;


use Sylius\Component\Core\Model\PaymentInterface;

interface PaymobServiceInterface
{
    /**
     * @param $payment
     * @param $paymentState
     * @param $orderPaymentState
     * @return mixed
     */
    public function setPaymentState($payment, $paymentState, $orderPaymentState);

    /**
     * @param $payment
     * @param $order
     * @return mixed
     */
    public function flushPaymentAndOrder($payment, $order);

    /**
     * @param $payment_id
     * @return PaymentInterface
     */
    public function getPaymentById($payment_id): PaymentInterface;
}
