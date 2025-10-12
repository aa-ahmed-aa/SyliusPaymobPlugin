<?php

declare(strict_types=1);

namespace Ahmedkhd\SyliusPaymobPlugin\Service;

use GuzzleHttp\Client as GuzzleClient;
use Payum\Core\Exception\UnsupportedApiException;
use Sylius\Component\Core\Model\PaymentInterface as SyliusPaymentInterface;
use Sylius\Component\Core\Repository\CustomerRepositoryInterface;
use Ahmedkhd\SyliusPaymobPlugin\Service\PaymobServiceInterface;
use Sylius\Component\Payment\Model\PaymentRequestInterface as SyliusPaymentRequestInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;
use Sylius\Component\Core\Repository\OrderRepositoryInterface;

final class PaymobService implements PaymobServiceInterface
{
    /** @var GuzzleClient */
    public GuzzleClient $client;

    public function __construct(
        private OrderRepositoryInterface $orderRepository,
        private CustomerRepositoryInterface $customerRepository,
        private RouterInterface $router,
    ) {
        $this->orderRepository = $orderRepository;
        $this->customerRepository = $customerRepository;
        $this->router = $router;
        $this->client = new GuzzleClient([
            'base_uri' => 'https://accept.paymobsolutions.com',
            'headers' => PaymobServiceInterface::HEADERS
        ]);
    }

    public function authenticate(SyliusPaymentRequestInterface $paymentRequest): string
    {
        $paymentConfig = $paymentRequest->getMethod()->getGatewayConfig()->getConfig();

        if ($this->client === null) {
            throw new UnsupportedApiException('Client not set. Expected an instance of ' . GuzzleClient::class);
        }

        $response = $this->client->post('/api/auth/tokens', [
            'json' => [
                'api_key' => $paymentConfig['api_key']
            ]
        ]);

        $body = $response->getBody()->getContents();
        
        return json_decode($body)->token ?? '';
    }

    public function createOrderId(SyliusPaymentRequestInterface $paymentRequest, string $token): string
    {
        $paymentConfig = $paymentRequest->getMethod()->getGatewayConfig()->getConfig();

        if ($this->client === null) {
            throw new UnsupportedApiException('Client not set. Expected an instance of ' . GuzzleClient::class);
        }

        $order = $paymentRequest->getPayment()->getOrder();

        // Get customer directly from customer repository - CREATE ORDER ID METHOD
        $customer = null;
        if ($order->getCustomer()) {
            $customer = $this->customerRepository->findOneBy(['id' => $order->getCustomer()->getId()]);
        }
        $billingAddress = $order->getBillingAddress();

        // Get customer data with fallbacks
        $firstName = $customer && $customer->getFirstName() ? $customer->getFirstName() : $order->getBillingAddress()->getFirstName();
        $lastName = $customer && $customer->getLastName() ? $customer->getLastName() : $order->getBillingAddress()->getLastName();
        $email = $customer && $customer->getEmail() ? $customer->getEmail() : "NA";
        $phoneNumber = $customer && $customer->getPhoneNumber() ? $customer->getPhoneNumber() : $order->getBillingAddress()->getPhoneNumber();

        // Get billing address data with fallbacks
        $street = $billingAddress && $billingAddress->getStreet() ? $billingAddress->getStreet() : "NA";
        $postalCode = $billingAddress && $billingAddress->getPostcode() ? $billingAddress->getPostcode() : "NA";
        $city = $billingAddress && $billingAddress->getCity() ? $billingAddress->getCity() : "NA";
        $country = $billingAddress && $billingAddress->getCountryCode() ? $billingAddress->getCountryCode() : "NA";
        $state = $billingAddress && $billingAddress->getProvinceName() ? $billingAddress->getProvinceName() : "NA";
        
        $conversionRate = json_decode(file_get_contents("https://cdn.jsdelivr.net/npm/@fawazahmed0/currency-api@latest/v1/currencies/egp.json"), true);

        $response = $this->client->post('/api/ecommerce/orders', [
            'json' => [
                'auth_token' => $token,
                'delivery_needed' => 'false',
                'amount_cents' => (string) floor($order->getCurrencyCode() === 'EGP' ? $order->getTotal() : ($order->getTotal() / $conversionRate["egp"][strtolower($order->getCurrencyCode())])),
                'currency' => "EGP",
                'merchant_id' => $paymentConfig['merchant_id'],
                'merchant_order_id' => $order->getNumber(),
                "shipping_data"=> [
                    "apartment"=> "NA",
                    'email'  => $email,
                    'phone_number'  => $phoneNumber,
                    "floor"=> "NA",
                    'first_name' => $firstName,
                    'last_name'  => $lastName,
                    'street' => $street,
                    "building"=> "NA",
                    'postal_code'  => $postalCode,
                    'city'  => $city,
                    'country'  => $country,
                    'state'  => $state,
                ]
            ]
        ]);

        $body = $response->getBody()->getContents();

        return (string) json_decode($body)->id ?? $body;
    }

    /**
     * Get the Payment Key from Paymob
     *
     * @param SyliusPaymentRequestInterface $paymentRequest
     * @param string $token
     * @param string $paymobOrderId
     * @return string
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getPaymentKey(SyliusPaymentRequestInterface $paymentRequest, string $token, string $paymobOrderId): string
    {
        $paymentConfig = $paymentRequest->getMethod()->getGatewayConfig()->getConfig();

        if ($this->client === null) {
            throw new UnsupportedApiException('Client not set. Expected an instance of ' . GuzzleClient::class);
        }

        $order = $paymentRequest->getPayment()->getOrder();

        // Get customer directly from customer repository - GET PAYMENT KEY METHOD
        $customer = null;
        if ($order->getCustomer()) {
            $customer = $this->customerRepository->findOneBy(['id' => $order->getCustomer()->getId()]);
        }
        $billingAddress = $order->getBillingAddress();

        // Get customer data with fallbacks
        $firstName = $customer && $customer->getFirstName() ? $customer->getFirstName() : $order ->getBillingAddress()->getFirstName();
        $lastName = $customer && $customer->getLastName() ? $customer->getLastName() : $order->getBillingAddress()->getLastName();
        $email = $customer && $customer->getEmail() ? $customer->getEmail() : "NA";
        $phoneNumber = $customer && $customer->getPhoneNumber() ? $customer->getPhoneNumber() : $order->getBillingAddress()->getPhoneNumber();

        // Get billing address data with fallbacks
        $street = $billingAddress && $billingAddress->getStreet() ? $billingAddress->getStreet() : "NA";
        $postalCode = $billingAddress && $billingAddress->getPostcode() ? $billingAddress->getPostcode() : "NA";
        $city = $billingAddress && $billingAddress->getCity() ? $billingAddress->getCity() : "NA";
        $country = $billingAddress && $billingAddress->getCountryCode() ? $billingAddress->getCountryCode() : "NA";
        $state = $billingAddress && $billingAddress->getProvinceName() ? $billingAddress->getProvinceName() : "NA";

        // Get shipping method safely
        $shipments = $order->getShipments();
        $shippingMethod = "NA";
        if ($shipments && count($shipments) > 0) {
            $firstShipment = $shipments->first();
            if ($firstShipment && $firstShipment->getMethod()) {
                $shippingMethod = $firstShipment->getMethod()->getName() ?: "NA";
            }
        }
        
        $conversionRate = json_decode(file_get_contents("https://cdn.jsdelivr.net/npm/@fawazahmed0/currency-api@latest/v1/currencies/egp.json"), true);
        $response = $this->client->post('/api/acceptance/payment_keys', [
            'json' => [
                'auth_token' => $token,
                'amount_cents' => (string) floor($order->getCurrencyCode() === 'EGP' ? $order->getTotal() : ($order->getTotal() / $conversionRate["egp"][strtolower($order->getCurrencyCode())])),
                'expiration' => '3600',
                'order_id' => $paymobOrderId,
                'currency' => "EGP",
                'merchant_id' => (string) $paymentConfig['merchant_id'],
                'integration_id' => (string) $paymentConfig['integration_id'],
                'redirection_url' => $this->router->generate(
                    'sylius_shop_order_after_pay',
                    ['hash' => $paymentRequest->getHash()->toBase58()],
                    UrlGeneratorInterface::ABSOLUTE_URL,
                ),
                "notification_url" => $this->router->generate(
                    'sylius_payment_method_notify',
                    ['code' => PaymobServiceInterface::GATEWAY_NAME],
                    UrlGeneratorInterface::ABSOLUTE_URL,
                ),
                'billing_data' => [
                    'first_name' => $firstName,
                    'last_name'  => $lastName,
                    'email'  => $email,
                    'phone_number'  => $phoneNumber,
                    'apartment'  => "NA",
                    'floor'  => 'NA',
                    'street'  => $street,
                    'building'  => 'NA',
                    'shipping_method' => $shippingMethod,
                    'postal_code'  => $postalCode,
                    'city'  => $city,
                    'country'  => $country,
                    'state'  => $state,
                ]
            ]
        ]);

        $body = $response->getBody()->getContents();

        return json_decode($body)->token ?? $body;
    }

    function getCurrentBaseURL(): string {
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') 
            || $_SERVER['SERVER_PORT'] == 443
            || $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https' // behind proxy
            ? "https://" 
            : "http://";

        $host = $_SERVER['HTTP_HOST'];

        return $protocol . $host;
    }
}