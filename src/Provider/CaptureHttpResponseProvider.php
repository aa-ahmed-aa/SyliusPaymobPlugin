<?php

declare(strict_types=1);

namespace Ahmedkhd\SyliusPaymobPlugin\Provider;

final class CaptureHttpResponseProvider
{
    public function supports(mixed $requestConfiguration, mixed $paymentRequest): bool
    {
        return $paymentRequest->getAction() === 'capture';
    }

    public function getResponse(mixed $requestConfiguration, mixed $paymentRequest): mixed
    {
        $data = $paymentRequest->getResponseData();

        // This will be implemented to return proper HTTP responses
        // For now, we'll return the data
        return $data;
    }
}
