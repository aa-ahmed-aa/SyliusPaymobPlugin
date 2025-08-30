<?php

namespace Ahmedkhd\SyliusPaymobPlugin\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

final class GatewayConfigurationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('api_key', TextType::class);
        $builder->add('hmac_security', TextType::class);
        $builder->add('merchant_id', TextType::class);
        $builder->add('iframe_id', TextType::class);
        $builder->add('integration_id', TextType::class);
    }
}