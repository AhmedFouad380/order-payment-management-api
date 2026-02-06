<?php

namespace Database\Seeders;

use App\Models\PaymentGateway;
use App\Services\Payments\Gateways\PaypalGateway;
use App\Services\Payments\Gateways\StripeGateway;
use Illuminate\Database\Seeder;

class PaymentGatewaySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        PaymentGateway::create([
            'name' => 'PayPal',
            'key' => 'paypal_express',
            'class' => PaypalGateway::class,
            'config' => [
                'client_id' => 'mock_paypal_client_id',
                'secret' => 'mock_paypal_secret',
                'env' => 'sandbox',
            ],
            'is_active' => true,
        ]);

        PaymentGateway::create([
            'name' => 'Stripe',
            'key' => 'stripe_cc',
            'class' => StripeGateway::class,
            'config' => [
                'api_key' => 'mock_stripe_key',
                'secret' => 'mock_stripe_secret',
            ],
            'is_active' => true,
        ]);
    }
}
