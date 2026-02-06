<?php

namespace App\Services\Payments\Gateways;

use App\Services\Payments\DTOs\PaymentResult;
use App\Services\Payments\PaymentGatewayInterface;
use Illuminate\Support\Str;

class PaypalGateway implements PaymentGatewayInterface
{
    public function charge(float $amount, array $config): PaymentResult
    {
        // Simulate API call to PayPal
        // Use config['client_id'] and config['secret']
        
        $success = true; // random success for mock? Let's make it always success for now unless amount is negative.
        
        if ($amount < 0) {
             return new PaymentResult(false, null, 'Invalid amount');
        }

        return new PaymentResult(
            true, 
            'PAYPAL-' . Str::uuid(), 
            'Payment processed successfully via PayPal'
        );
    }
}
