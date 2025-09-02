<?php

namespace Ahmedkhd\SyliusPaymobPlugin\Model;

interface PaymobInterface
{
    public function getApiKey(): string;

    public function getHmacSecurity(): string;

    public function getMerchantId(): string;

    public function getIframe(): string;

    public function getIntegrationId(): string;
}
