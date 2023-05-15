<p align="center">
    <a href="https://sylius.com" target="_blank">
        <img src="https://demo.sylius.com/assets/shop/img/logo.png" />
    </a>
</p>

<h1 align="center">Sylius Paymob Plugin</h1>

<p align="center">Sylius plugin for payment gateway Paymob operating in Egypt.</p>


### !!!! :warning: Sylius Version Support

- `master` branch supports sylius version 1.8 & 1.9
- `1.12` branch supports sylius version 1.12

## Quickstart Installation

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

Add routing to your `config/routes/sylius_shop.yaml`

```yaml
ahmedkhd_sylius_paymob_plugin_notify_url:
    resource: "@AhmedkhdSyliusPaymobPlugin/config/shop_routing.yml"
```

Add config to your `config/packages/_sylius.yaml`

```yml
imports:
 ...
    - { resource: "@AhmedkhdSyliusPaymobPlugin/Resources/config/config.yml" }
```

### On Paymob
under `payment integrations` tab click edit on you environment and add these urls
##### Transaction processed callback
https://{domain_name}/payment/paymob/webhook


### Develop this plugin
check [development guide](https://github.com/aa-ahmed-aa/SyliusPaymobPlugin/blob/1.12/Development.md)
