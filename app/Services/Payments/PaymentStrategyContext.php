<?php

namespace App\Services\Payments;

use App\Models\PaymentGateway;
use Exception;

class PaymentStrategyContext
{
    /**
     * Resolve the gateway instance based on the key.
     *
     * @param string $gatewayKey
     * @return PaymentGatewayInterface
     * @throws Exception
     */
    public function resolve(string $gatewayKey): PaymentGatewayInterface
    {
        $gatewayConfig = PaymentGateway::where('key', $gatewayKey)->where('is_active', true)->first();

        if (!$gatewayConfig) {
            throw new Exception("Payment gateway [{$gatewayKey}] not found or inactive.");
        }

        if (!class_exists($gatewayConfig->class)) {
             throw new Exception("Gateway class [{$gatewayConfig->class}] does not exist.");
        }

        // Pass config to constructor if needed, or just instantiate. 
        // Our interface passes config to method charge(), so simple instantiation is fine.
        return new $gatewayConfig->class();
    }
}
