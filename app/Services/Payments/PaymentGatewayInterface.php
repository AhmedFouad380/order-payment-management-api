<?php

namespace App\Services\Payments;

use App\Services\Payments\DTOs\PaymentResult;

interface PaymentGatewayInterface
{
    /**
     * Charge the customer.
     *
     * @param float $amount
     * @param array $config Gateway specific configuration (api keys, secrets)
     * @return PaymentResult
     */
    public function charge(float $amount, array $config): PaymentResult;
}
