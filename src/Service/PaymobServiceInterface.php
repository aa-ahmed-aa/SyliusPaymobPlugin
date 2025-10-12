<?php

declare(strict_types=1);

namespace Ahmedkhd\SyliusPaymobPlugin\Service;

use Sylius\Component\Core\Model\PaymentInterface as SyliusPaymentInterface;
use Sylius\Component\Payment\Model\PaymentRequestInterface as SyliusPaymentRequestInterface;
use Ahmedkhd\SyliusPaymobPlugin\Model\PaymobInterface;
use Sylius\Component\Order\Model\OrderInterface;

interface PaymobServiceInterface
{
    /**
     * Static headers for Paymob requests
     */
    public const HEADERS = [
        'Accept'     => '*/*',
        'Content-Type' => 'application/json'
    ];

    /**
     * Static for Paymob
     */
    public const TRANSACTION_TYPE = 'TRANSACTION';

    /**
     * Static gateway name for Paymob
     */
    public const GATEWAY_NAME = 'paymob';

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
    public function createOrderId(SyliusPaymentRequestInterface $paymentRequest, string $token): string;

    /**
     * Get the iFrame token from Paymob
     *
     * @param SyliusPaymentInterface $payment
     * @param string $token
     * @param string $orderId
     * @return string
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getPaymentKey(SyliusPaymentRequestInterface $paymentRequest, string $token, string $paymobOrderId): string;
}
