<?php

namespace App\Services\Payments\Gateways;

use App\Services\Payments\DTOs\PaymentResult;
use App\Services\Payments\PaymentGatewayInterface;
use Illuminate\Support\Str;

class StripeGateway implements PaymentGatewayInterface
{
    public function charge(float $amount, array $config): PaymentResult
    {
        // Simulate Stripe Charge
        
        return new PaymentResult(
            true, 
            'ch_' . Str::random(24), 
            'Payment processed successfully via Stripe'
        );
    }
}
