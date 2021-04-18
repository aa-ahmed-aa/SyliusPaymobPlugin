<p align="center">
    <a href="https://sylius.com" target="_blank">
        <img src="https://demo.sylius.com/assets/shop/img/logo.png" />
    </a>
</p>

# Sylius Paymob payment gateway plugin  

## Installation

```bash
composer require aa-ahmed-aa/sylius-paymob-plugin
```


Add plugin dependencies to your config/bundles.php file:

```php
return [
    ...
    Ahmedkhd\SyliusPaymobPlugin\AhmedkhdSyliusPaymobPlugin::class => ['all'=>true]
];
```

Add routing to your config/routes/sylius_shop.yaml

```yaml
ahmedkhd_sylius_paymob_plugin_notify_url:
    resource: "@AhmedkhdSyliusPaymobPlugin/Resources/config/routes.yml"
```

Add config to your config/packages/_sylius.yaml

```yml
imports:
 ...
    - { resource: "@AhmedkhdSyliusPaymobPlugin/Resources/config/config.yml" }
```

Return url is :
https://{domain_name}/payment/paymob/capture
