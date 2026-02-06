<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Models\Payment;
use App\Services\Payments\PaymentStrategyContext;
use Exception;
use Illuminate\Support\Facades\DB;

class PaymentService
{
    public function __construct(protected PaymentStrategyContext $strategyContext) {}

    public function processPayment(Order $order, string $gatewayKey): Payment
    {
        // Business Rule: Order must be confirmed
        if ($order->status !== OrderStatus::CONFIRMED) {
            throw new Exception("Payments can only be processed for confirmed orders.");
        }

        // Resolve Gateway
        $gateway = $this->strategyContext->resolve($gatewayKey);

        // Charge
        // Config could be passed from DB (inside resolve or passed here). 
        // My resolve() loads config and passes it? No, my resolve() instantiated the class.
        // The Gateway Interface expects `charge($amount, $config)`.
        // So I need the config array.
        // I should update resolve() to return the config too, or the Gateway instance should already have it?
        // Clean Code: The Gateway Factory should probably inject the config into the Gateway instance if it's "Context-aware".
        // But my Interface says `charge($amount, $config)`.
        // So I should fetch config here or let resolve handle it.
        // Let's explicitly fetch config here for clarity or better yet, make `resolve` return a DTO with instance and config?
        // Or cleaner: `PaymentStrategyContext` implementation I wrote earlier:
        // `return new $gatewayConfig->class();`
        // It doesn't pass config.
        // Let's fetch config in this service.
        $gatewayModel = \App\Models\PaymentGateway::where('key', $gatewayKey)->firstOrFail();
        
        $result = $gateway->charge($order->total_amount, $gatewayModel->config ?? []);

        // Record Payment
        return Payment::create([
            'order_id' => $order->id,
            'gateway_key' => $gatewayKey,
            'transaction_id' => $result->transactionId,
            'amount' => $order->total_amount,
            'status' => $result->success ? PaymentStatus::SUCCESSFUL : PaymentStatus::FAILED,
            'response_data' => ['message' => $result->message, 'data' => $result->data],
        ]);
    }
}
