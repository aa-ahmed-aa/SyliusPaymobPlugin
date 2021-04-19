<?php

declare(strict_types=1);

namespace Ahmedkhd\SyliusPaymobPlugin\Payum\Action;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Exception\UnsupportedApiException;
use Sylius\Component\Core\Model\PaymentInterface;
use Sylius\Component\Core\Model\PaymentInterface as SyliusPaymentInterface;
use Payum\Core\Request\Capture;
use Ahmedkhd\SyliusPaymobPlugin\Payum\SyliusApi;

final class CaptureAction implements ActionInterface, ApiAwareInterface
{
    /** @var Client */
    private $client;

    /** @var SyliusApi */
    private $api;

    /** @var array */
    private $headers;

    public function __construct(Client $client)
    {
        $this->client = $client;
        $this->headers['headers'] = [
            'Accept'     => '*/*',
            'Content-Type' => 'application/json'
        ];
    }

    public function execute($request): void
    {
        RequestNotSupportedException::assertSupports($this, $request);

        /** @var SyliusPaymentInterface $payment */
        $payment = $request->getModel();

        try {
            $authToken = $this->authenticate();
            $orderId = $this->createOrderId($payment, $authToken);
            $paymentToken = $this->getPaymentKey($payment, $authToken, strval($orderId));
            $iframeURL = "https://accept.paymobsolutions.com/api/acceptance/iframes/{$this->api->getIframe()}?payment_token={$paymentToken}";
        } catch (RequestException $exception) {
            $payment->setDetails(['status'=> "failed", "message" => $exception->getMessage()]);
            $payment->setState(PaymentInterface::STATE_FAILED);
            return;
        }

        $this->api->doPayment($iframeURL);
    }

    public function supports($request): bool
    {
        return
            $request instanceof Capture &&
            $request->getModel() instanceof SyliusPaymentInterface
            ;
    }

    public function setApi($api): void
    {
        if (!$api instanceof SyliusApi) {
            throw new UnsupportedApiException('Not supported. Expected an instance of ' . SyliusApi::class);
        }

        $this->api = $api;
    }

    /**
     * Get the Authentication Token from Paymob
     *
     * @return string
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    private function authenticate()
    {
        $response = $this->client->request('POST', 'https://accept.paymobsolutions.com/api/auth/tokens',
            $this->getBodyWithHeader([
                'body' => \GuzzleHttp\json_encode([
                    'api_key' => $this->api->getApiKey()
                ])
            ])
        );

        return \GuzzleHttp\json_decode($response->getBody()->getContents())->token ?? '';
    }

    /**
     * Get the OrderId from Paymob
     *
     * @param SyliusPaymentInterface $payment
     * @param string $token
     * @return string
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    private function createOrderId(SyliusPaymentInterface $payment,string $token)
    {
        $response = $this->client->request('POST', 'https://accept.paymobsolutions.com/api/ecommerce/orders',
            $this->getBodyWithHeader([
                'body' => \GuzzleHttp\json_encode([
                    'auth_token' => $token,
                    'delivery_needed' => 'false',
                    'amount_cents' => intval($payment->getAmount()*100),
                    'currency' => "EGP",
                    'merchant_id' => $this->api->getMerchantId(),
                    'merchant_order_id' => $payment->getId(),
                    "shipping_data"=> [
                        "apartment"=> "NA",
                        'email'  => $payment->getOrder()->getCustomer()->getEmail() ?? "NA",
                        'phone_number'  => $payment->getOrder()->getCustomer()->getPhoneNumber() ?? "NA",
                        "floor"=> "NA",
                        'first_name' => $payment->getOrder()->getCustomer()->getFirstName() ?? "NA",
                        'last_name'  => $payment->getOrder()->getCustomer()->getLastName() ?? "NA",
                        $payment->getOrder()->getBillingAddress()->getStreet() ?? "NA",
                        "building"=> "NA",
                        'postal_code'  => $payment->getOrder()->getBillingAddress()->getPostcode() ?? "NA",
                        'city'  => $payment->getOrder()->getBillingAddress()->getCity() ?? "NA",
                        'country'  => $payment->getOrder()->getBillingAddress()->getCountryCode() ?? "NA",
                        'state'  => $payment->getOrder()->getBillingAddress()->getProvinceName() ?? "NA",
                    ]
                ])
            ])
        )->getBody()->getContents();

        return \GuzzleHttp\json_decode($response)->id ?? $response;
    }

    /**
     * Get th e iFrame token from Paymob
     * @param SyliusPaymentInterface $payment
     * @param string $token
     * @param string $orderId
     * @return \Psr\Http\Message\ResponseInterface
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    private function getPaymentKey(SyliusPaymentInterface $payment,string $token,string $orderId)
    {
        $response = $this->client->request('POST', 'https://accept.paymobsolutions.com/api/acceptance/payment_keys',
            $this->getBodyWithHeader([
                'body' => \GuzzleHttp\json_encode([
                    'auth_token' => $token,
                    'amount_cents' => intval($payment->getAmount()*100),
                    'expiration' => '3600',
                    'order_id' => $orderId,
                    'currency' => "EGP",
                    'merchant_id' => $this->api->getMerchantId(),
                    'integration_id' => $this->api->getIntegrationId(),
                    'billing_data' => [
                        'first_name' => $payment->getOrder()->getCustomer()->getFirstName() ?? "NA",
                        'last_name'  => $payment->getOrder()->getCustomer()->getLastName() ?? "NA",
                        'email'  => $payment->getOrder()->getCustomer()->getEmail() ?? "NA",
                        'phone_number'  => $payment->getOrder()->getCustomer()->getPhoneNumber() ?? "NA",
                        'apartment'  => "NA",
                        'floor'  => 'NA',
                        'street'  => $payment->getOrder()->getBillingAddress()->getStreet() ?? "NA",
                        'building'  => 'NA',
                        'shipping_method' => $payment->getOrder()->getShipments()->toArray()[0]->getMethod()->getName() ?? "NA",
                        'postal_code'  => $payment->getOrder()->getBillingAddress()->getPostcode() ?? "NA",
                        'city'  => $payment->getOrder()->getBillingAddress()->getCity() ?? "NA",
                        'country'  => $payment->getOrder()->getBillingAddress()->getCountryCode() ?? "NA",
                        'state'  => $payment->getOrder()->getBillingAddress()->getProvinceName() ?? "NA",
                    ]
                ])
            ])
        );

        return \GuzzleHttp\json_decode($response->getBody()->getContents())->token ?? $response;
    }

    public function getBodyWithHeader($body)
    {
        return array_merge($body, $this->headers);
    }
}
