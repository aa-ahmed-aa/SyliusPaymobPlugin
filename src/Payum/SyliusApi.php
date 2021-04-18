<?php

declare(strict_types=1);

namespace Ahmedkhd\SyliusPaymobPlugin\Payum;


final class SyliusApi
{
    /** @var string */
    private $apiKey;

    /** @var string */
    private $hamcSecurity;

    /** @var string */
    private $merchantId;

    /** @var string */
    private $iframe;

    /** @var string */
    private $integrationId;


    public function __construct(
        string $apiKey,
        string $hamcSecurity,
        string $merchantId,
        string $iframe,
        string $integrationId
    )
    {
        $this->apiKey = $apiKey;
        $this->hamcSecurity = $hamcSecurity;
        $this->merchantId = $merchantId;
        $this->iframe = $iframe;
        $this->integrationId = $integrationId;
    }

    public function getApiKey(): string
    {
        return $this->apiKey;
    }

    /**
     * @return string
     */
    public function getHamcSecurity(): string
    {
        return $this->hamcSecurity;
    }

    /**
     * @return string
     */
    public function getMerchantId(): string
    {
        return $this->merchantId;
    }

    /**
     * @return string
     */
    public function getIframe(): string
    {
        return $this->iframe;
    }

    /**
     * @return string
     */
    public function getIntegrationId(): string
    {
        return $this->integrationId;
    }

    public function doPayment($iframeURL)
    {
        header("location: {$iframeURL}");
        exit;
    }

}
