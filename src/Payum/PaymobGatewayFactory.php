<?php

declare(strict_types=1);

namespace Ahmedkhd\SyliusPaymobPlugin\Payum;

use Ahmedkhd\SyliusPaymobPlugin\Payum\Action\CaptureAction;
use Ahmedkhd\SyliusPaymobPlugin\Payum\Action\StatusAction;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\GatewayFactory;

final class PaymobGatewayFactory extends GatewayFactory
{
    protected function populateConfig(ArrayObject $config): void
    {
        $config->defaults([
            'payum.factory_name' => 'paymob',
            'payum.factory_title' => 'Paymob',
            'payum.action.status' => new StatusAction(),
        ]);

        $config['payum.api'] = function (ArrayObject $config) {
            return new SyliusApi(
                $config['api_key'],
                $config['hamc_security'],
                $config['merchant_id'],
                $config['iframe'],
                $config['integration_id']
            );
        };
    }
}
