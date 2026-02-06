<?php

namespace Tests\Unit;

use App\Models\PaymentGateway as GatewayModel;
use App\Services\Payments\Gateways\PaypalGateway;
use App\Services\Payments\PaymentStrategyContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentStrategyContextTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_resolves_paypal_gateway()
    {
        // Arrange
        GatewayModel::create([
            'name' => 'PayPal',
            'key' => 'paypal_express',
            'class' => PaypalGateway::class,
            'config' => ['client_id' => 'foo', 'secret' => 'bar'],
            'is_active' => true,
        ]);

        $context = new PaymentStrategyContext();

        // Act
        $gateway = $context->resolve('paypal_express');

        // Assert
        $this->assertInstanceOf(PaypalGateway::class, $gateway);
    }

    public function test_it_throws_exception_if_gateway_not_found()
    {
        $this->expectException(\Exception::class);
        
        $context = new PaymentStrategyContext();
        $context->resolve('non_existent');
    }
}
