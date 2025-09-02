<?php

declare(strict_types=1);

namespace Ahmedkhd\SyliusPaymobPlugin\Service;

use GuzzleHttp\Client as GuzzleClient;
use Payum\Core\Exception\UnsupportedApiException;
use Sylius\Component\Core\Model\PaymentInterface as SyliusPaymentInterface;
use Sylius\Component\Core\Repository\OrderRepositoryInterface;
use Sylius\Component\Core\Repository\CustomerRepositoryInterface;
use Ahmedkhd\SyliusPaymobPlugin\Model\PaymobInterface;
use Ahmedkhd\SyliusPaymobPlugin\Model\Paymob;
use Ahmedkhd\SyliusPaymobPlugin\Service\PaymobServiceInterface;


final class PaymobService implements PaymobServiceInterface
{

    public static $HEADERS = [
        'Accept'     => '*/*',
        'Content-Type' => 'application/json'
    ];

    /** @var PaymobInterface */
    public PaymobInterface $paymobConfig;

    /** @var GuzzleClient */
    public GuzzleClient $client;

    /** @var OrderRepositoryInterface */
    private OrderRepositoryInterface $orderRepository;

    /** @var CustomerRepositoryInterface */
    private CustomerRepositoryInterface $customerRepository;

    public function __construct(
        OrderRepositoryInterface $orderRepository,
        CustomerRepositoryInterface $customerRepository
    ) {
        $this->orderRepository = $orderRepository;
        $this->customerRepository = $customerRepository;
        $this->client = new GuzzleClient([
            'base_uri' => 'https://accept.paymobsolutions.com',
            'headers' => self::$HEADERS
        ]);
    }
    
    public function setPaymobConfig(PaymobInterface $paymobConfig): void
    {
        $this->paymobConfig = new Paymob(
            $paymobConfig->getApiKey(),
            $paymobConfig->getHmacSecurity(),
            $paymobConfig->getMerchantId(),
            $paymobConfig->getIframe(),
            $paymobConfig->getIntegrationId()
        );
    }

    public function getPaymobConfig(): ?PaymobInterface
    {
        return $this->paymobConfig;
    }

    /**
     * Get the Authentication Token from Paymob
     *
     * @return string
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function authenticate(): string
    {
        if ($this->client === null) {
            throw new UnsupportedApiException('Client not set. Expected an instance of ' . GuzzleClient::class);
        }

        $response = $this->client->post('/api/auth/tokens', [
            'json' => [
                'api_key' => $this->getPaymobConfig()->getApiKey()
            ]
        ]);

        $body = $response->getBody()->getContents();
        
        return json_decode($body)->token ?? '';
    }

    /**
     * Get the OrderId from Paymob
     *
     * @param SyliusPaymentInterface $payment
     * @param string $token
     * @return string
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
        public function createOrderId(SyliusPaymentInterface $payment, string $token): string
    {
        if ($this->client === null) {
            throw new UnsupportedApiException('Client not set. Expected an instance of ' . GuzzleClient::class);
        }

        // Debug payment data - CREATE ORDER ID METHOD
        error_log("Payment ID: " . $payment->getId());
        error_log("Payment Order ID: " . $payment->getOrder()->getId());
        
        // Find the order with all necessary associations loaded
        $order = $this->orderRepository->findOneById(["id" => $payment->getOrder()->getId()]);
        // Get customer directly from customer repository - CREATE ORDER ID METHOD
        $customer = null;
        if ($order->getCustomer()) {
            $customer = $this->customerRepository->findOneBy(['id' => $order->getCustomer()->getId()]);
            error_log("Customer found by ID: " . ($customer ? "yes" : "no"));
        }
        $billingAddress = $order->getBillingAddress();

        // Get customer data with fallbacks
        $firstName = $customer && $customer->getFirstName() ? $customer->getFirstName() : "NA";
        $lastName = $customer && $customer->getLastName() ? $customer->getLastName() : "NA";
        $email = $customer && $customer->getEmail() ? $customer->getEmail() : "NA";
        $phoneNumber = $customer && $customer->getPhoneNumber() ? $customer->getPhoneNumber() : "NA";

        // Get billing address data with fallbacks
        $street = $billingAddress && $billingAddress->getStreet() ? $billingAddress->getStreet() : "NA";
        $postalCode = $billingAddress && $billingAddress->getPostcode() ? $billingAddress->getPostcode() : "NA";
        $city = $billingAddress && $billingAddress->getCity() ? $billingAddress->getCity() : "NA";
        $country = $billingAddress && $billingAddress->getCountryCode() ? $billingAddress->getCountryCode() : "NA";
        $state = $billingAddress && $billingAddress->getProvinceName() ? $billingAddress->getProvinceName() : "NA";
       
        $response = $this->client->post('/api/ecommerce/orders', [
            'json' => [
                'auth_token' => $token,
                'delivery_needed' => 'false',
                'amount_cents' => (string) $order->getTotal(),
                'currency' => "EGP",
                'merchant_id' => (string) $this->getPaymobConfig()->getMerchantId(),
                'merchant_order_id' => (string) $payment->getId(),
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
     * Get the iFrame token from Paymob
     */
    public function getPaymentKey(SyliusPaymentInterface $payment, string $token, string $orderId): string
    {
        if ($this->client === null) {
            throw new UnsupportedApiException('Client not set. Expected an instance of ' . GuzzleClient::class);
        }

        // Find the order with all necessary associations loaded
        $order = $this->orderRepository->createQueryBuilder('o')
            ->leftJoin('o.customer', 'c')
            ->leftJoin('o.billingAddress', 'ba')
            ->leftJoin('o.shipments', 's')
            ->leftJoin('s.method', 'sm')
            ->addSelect('c', 'ba', 's', 'sm')
            ->where('o.id = :orderId')
            ->setParameter('orderId', $payment->getOrder()->getId())
            ->getQuery()
            ->getSingleResult();
        
        // Get customer directly from customer repository - GET PAYMENT KEY METHOD
        $customer = null;
        if ($order->getCustomer()) {
            $customer = $this->customerRepository->findOneBy(['id' => $order->getCustomer()->getId()]);
            error_log("Customer found by ID in getPaymentKey: " . ($customer ? "yes" : "no"));
        }
        $billingAddress = $order->getBillingAddress();

        // Get customer data with fallbacks
        $firstName = $customer && $customer->getFirstName() ? $customer->getFirstName() : "NA";
        $lastName = $customer && $customer->getLastName() ? $customer->getLastName() : "NA";
        $email = $customer && $customer->getEmail() ? $customer->getEmail() : "NA";
        $phoneNumber = $customer && $customer->getPhoneNumber() ? $customer->getPhoneNumber() : "NA";

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

        // Get the current domain for callback URLs
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $baseUrl = "{$protocol}://{$host}";
        
        $response = $this->client->post('/api/acceptance/payment_keys', [
            'json' => [
                'auth_token' => $token,
                'amount_cents' => (string) $order->getTotal(),
                'expiration' => '3600',
                'order_id' => $orderId,
                'currency' => "EGP",
                'merchant_id' => (string) $this->getPaymobConfig()->getMerchantId(),
                'integration_id' => (string) $this->getPaymobConfig()->getIntegrationId(),
                'success_url' => "{$baseUrl}/payment/paymob/capture?success=true&order={$orderId}",
                'failure_url' => "{$baseUrl}/payment/paymob/capture?success=false&order={$orderId}",
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

    public function doPayment(string $iframeURL): void
    {
        header("location: {$iframeURL}");
        exit;
    }

}