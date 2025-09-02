<?php

declare(strict_types=1);

namespace Ahmedkhd\SyliusPaymobPlugin\Service;

use Sylius\Component\Core\Model\PaymentInterface as SyliusPaymentInterface;
use Ahmedkhd\SyliusPaymobPlugin\Model\PaymobInterface;

interface PaymobServiceInterface
{
    /**
     * Set the Paymob configuration
     *
     * @param PaymobInterface $paymobConfig
     * @return void
     */
    public function setPaymobConfig(PaymobInterface $paymobConfig): void;

    /**
     * Get the Paymob configuration
     *
     * @return PaymobInterface|null
     */
    public function getPaymobConfig(): ?PaymobInterface;

    /**
     * Get the Authentication Token from Paymob
     *
     * @return string
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function authenticate(): string;

    /**
     * Get the OrderId from Paymob
     *
     * @param SyliusPaymentInterface $payment
     * @param string $token
     * @return string
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function createOrderId(SyliusPaymentInterface $payment, string $token): string;

    /**
     * Get the iFrame token from Paymob
     *
     * @param SyliusPaymentInterface $payment
     * @param string $token
     * @param string $orderId
     * @return string
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getPaymentKey(SyliusPaymentInterface $payment, string $token, string $orderId): string;

    /**
     * Redirect to payment iframe or handle payment redirection logic.
     *
     * @param string $iframeURL
     * @return void
     */
    public function doPayment(string $iframeURL): void;
}
