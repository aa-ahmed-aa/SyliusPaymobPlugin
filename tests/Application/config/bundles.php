<?php

return [
    Symfony\Bundle\FrameworkBundle\FrameworkBundle::class => ['all' => true],
    Symfony\Bundle\MonologBundle\MonologBundle::class => ['all' => true],
    Symfony\Bundle\SecurityBundle\SecurityBundle::class => ['all' => true],
    // Symfony\Bundle\SwiftmailerBundle\SwiftmailerBundle::class => ['all' => true], // Commented out - not available in Sylius 2.1
    Symfony\Bundle\TwigBundle\TwigBundle::class => ['all' => true],
    Doctrine\Bundle\DoctrineBundle\DoctrineBundle::class => ['all' => true],
    Sylius\Bundle\OrderBundle\SyliusOrderBundle::class => ['all' => true],
    Sylius\Bundle\MoneyBundle\SyliusMoneyBundle::class => ['all' => true],
    Sylius\Bundle\CurrencyBundle\SyliusCurrencyBundle::class => ['all' => true],
    Sylius\Bundle\LocaleBundle\SyliusLocaleBundle::class => ['all' => true],
    Sylius\Bundle\ProductBundle\SyliusProductBundle::class => ['all' => true],
    Sylius\Bundle\ChannelBundle\SyliusChannelBundle::class => ['all' => true],
    Sylius\Bundle\AttributeBundle\SyliusAttributeBundle::class => ['all' => true],
    Sylius\Bundle\TaxationBundle\SyliusTaxationBundle::class => ['all' => true],
    Sylius\Bundle\ShippingBundle\SyliusShippingBundle::class => ['all' => true],
    Sylius\Bundle\PaymentBundle\SyliusPaymentBundle::class => ['all' => true],
    Sylius\Bundle\MailerBundle\SyliusMailerBundle::class => ['all' => true],
    Sylius\Bundle\PromotionBundle\SyliusPromotionBundle::class => ['all' => true],
    Sylius\Bundle\AddressingBundle\SyliusAddressingBundle::class => ['all' => true],
    Sylius\Bundle\InventoryBundle\SyliusInventoryBundle::class => ['all' => true],
    Sylius\Bundle\TaxonomyBundle\SyliusTaxonomyBundle::class => ['all' => true],
    Sylius\Bundle\UserBundle\SyliusUserBundle::class => ['all' => true],
    Sylius\Bundle\CustomerBundle\SyliusCustomerBundle::class => ['all' => true],
    Sylius\Bundle\UiBundle\SyliusUiBundle::class => ['all' => true],
    Sylius\Bundle\ReviewBundle\SyliusReviewBundle::class => ['all' => true],
    Sylius\Bundle\CoreBundle\SyliusCoreBundle::class => ['all' => true],
    Sylius\Bundle\ResourceBundle\SyliusResourceBundle::class => ['all' => true],
    Sylius\Bundle\GridBundle\SyliusGridBundle::class => ['all' => true],
    // winzou\Bundle\StateMachineBundle\winzouStateMachineBundle::class => ['all' => true], // Commented out - not available in Sylius 2.1
    // Sonata\BlockBundle\SonataBlockBundle::class => ['all' => true], // Commented out - not available in Sylius 2.1
    // Bazinga\Bundle\HateoasBundle\BazingaHateoasBundle::class => ['all' => true], // Commented out - not available in Sylius 2.1
    // JMS\SerializerBundle\JMSSerializerBundle::class => ['all' => true], // Commented out - not available in Sylius 2.1
    // FOS\RestBundle\FOSRestBundle::class => ['all' => true], // Commented out - not available in Sylius 2.1
    // Knp\Bundle\GaufretteBundle\KnpGaufretteBundle::class => ['all' => true], // Commented out - not available in Sylius 2.1
    // Knp\Bundle\MenuBundle\KnpMenuBundle::class => ['all' => true], // Commented out - not available in Sylius 2.1
    // Liip\ImagineBundle\LiipImagineBundle::class => ['all' => true], // Commented out - not available in Sylius 2.1
    // Payum\Bundle\PayumBundle\PayumBundle::class => ['all' => true], // Commented out - not available in Sylius 2.1
    // Stof\DoctrineExtensionsBundle\StofDoctrineExtensionsBundle::class => ['all' => true], // Commented out - not available in Sylius 2.1
    Doctrine\Bundle\MigrationsBundle\DoctrineMigrationsBundle::class => ['all' => true],
    Sylius\Bundle\FixturesBundle\SyliusFixturesBundle::class => ['all' => true],
    // Sylius\Bundle\PayumBundle\SyliusPayumBundle::class => ['all' => true], // Commented out - not available in Sylius 2.1
    Sylius\Bundle\ThemeBundle\SyliusThemeBundle::class => ['all' => true],
    Sylius\Bundle\AdminBundle\SyliusAdminBundle::class => ['all' => true],
    Sylius\Bundle\ShopBundle\SyliusShopBundle::class => ['all' => true],
    // FOS\OAuthServerBundle\FOSOAuthServerBundle::class => ['all' => true], // Commented out - not available in Sylius 2.1
    // Sylius\Bundle\AdminApiBundle\SyliusAdminApiBundle::class => ['all' => true], // Commented out - not available in Sylius 2.1
    Ahmedkhd\SyliusPaymobPlugin\AhmedkhdSyliusPaymobPlugin::class => ['all' => true],
    Symfony\Bundle\DebugBundle\DebugBundle::class => ['dev' => true, 'test' => true, 'test_cached' => true],
    Symfony\Bundle\WebProfilerBundle\WebProfilerBundle::class => ['dev' => true, 'test' => true, 'test_cached' => true],
    FriendsOfBehat\SymfonyExtension\Bundle\FriendsOfBehatSymfonyExtensionBundle::class => ['test' => true, 'test_cached' => true],
    Sylius\Behat\Application\SyliusTestPlugin\SyliusTestPlugin::class => ['test' => true, 'test_cached' => true],
    // ApiPlatform\Core\Bridge\Symfony\Bundle\ApiPlatformBundle::class => ['all' => true], // Commented out - not available in Sylius 2.1
    // Lexik\Bundle\JWTAuthenticationBundle\LexikJWTAuthenticationBundle::class => ['all' => true], // Commented out - not available in Sylius 2.1
    // Sylius\Bundle\ApiBundle\SyliusApiBundle::class => ['all' => true], // Commented out - not available in Sylius 2.1
    SyliusLabs\DoctrineMigrationsExtraBundle\SyliusLabsDoctrineMigrationsExtraBundle::class => ['all' => true],
    Symplify\ConsoleColorDiff\ConsoleColorDiffBundle::class => ['dev' => true, 'test' => true],
];
