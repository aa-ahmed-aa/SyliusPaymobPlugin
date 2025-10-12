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

Add config to your `config/packages/_sylius.yaml`

```yml
imports:
 ...
    - { resource: "@AhmedkhdSyliusPaymobPlugin/config/config.yaml" }
```

### On Paymob
under `payment integrations` tab click edit on you environment and add these urls
##### Transaction processed callback
https://{domain_name}/payment-methods/paymob

##### Transaction response callback
https://{domain_name}/payment-methods/paymob
