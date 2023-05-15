<?php

declare(strict_types=1);

namespace Ahmedkhd\SyliusPaymobPlugin\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

final class SyliusGatewayConfigurationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('api_key', TextType::class);
        $builder->add('hamc_security', TextType::class);
        $builder->add('merchant_id', TextType::class);
        $builder->add('iframe', TextType::class);
        $builder->add('integration_id', TextType::class);
    }
}
