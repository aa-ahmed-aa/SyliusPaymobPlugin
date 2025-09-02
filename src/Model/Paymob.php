<?php

namespace Ahmedkhd\SyliusPaymobPlugin\Model;

use Ahmedkhd\SyliusPaymobPlugin\Model\PaymobInterface;

final class Paymob implements PaymobInterface
{
   /** @var string */
   private $apiKey;

   /** @var string */
   private $hmacSecurity;

   /** @var string */
   private $merchantId;

   /** @var string */
   private $iframe;

   /** @var string */
   private $integrationId;


   public function __construct(
       string $apiKey,
       string $hmacSecurity,
       string $merchantId,
       string $iframe,
       string $integrationId
   )
   {
       $this->apiKey = $apiKey;
       $this->hmacSecurity = $hmacSecurity;
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
   public function getHmacSecurity(): string
   {
       return $this->hmacSecurity;
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
}