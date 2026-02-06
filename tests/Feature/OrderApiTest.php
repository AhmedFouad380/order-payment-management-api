<?php

namespace Tests\Feature;

use App\Models\PaymentGateway;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;

class OrderApiTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $token;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Register & Login setup
        $this->user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $this->token = $response->json('access_token');
    }

    public function test_user_can_create_order()
    {
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->postJson('/api/orders', [
                'items' => [
                    [
                        'product_name' => 'Test Product',
                        'quantity' => 2,
                        'unit_price' => 50.00
                    ]
                ],
                'currency' => 'USD'
            ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.total_amount', 100); // 2 * 50 as integer/float
    }

    public function test_user_can_pay_for_confirmed_order()
    {
        // 1. Setup Gateway
        PaymentGateway::create([
            'name' => 'PayPal',
            'key' => 'paypal_express',
            'class' => \App\Services\Payments\Gateways\PaypalGateway::class,
            'is_active' => true,
        ]);

        // 2. Create Order
        $createResponse = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->postJson('/api/orders', [
                'items' => [['product_name' => 'Item', 'quantity' => 1, 'unit_price' => 10]]
            ]);
        
        $orderId = $createResponse->json('data.id'); 

        // 3. Confirm Order (Mock Admin)
        $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->putJson("/api/orders/{$orderId}", ['status' => 'confirmed'])
            ->assertStatus(200);

        // 4. Pay
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->postJson("/api/orders/{$orderId}/pay", [ 
                'gateway_key' => 'paypal_express'
            ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.status', 'successful');
    }

    public function test_cannot_delete_order_with_payment()
    {
        // Setup Gateway & Order & Pay
        PaymentGateway::create([
            'name' => 'PayPal',
            'key' => 'paypal_express',
            'class' => \App\Services\Payments\Gateways\PaypalGateway::class,
            'is_active' => true,
        ]);

        $createResponse = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->postJson('/api/orders', ['items' => [['product_name' => 'A', 'quantity' => 1, 'unit_price' => 10]]]);
        
        $orderId = $createResponse->json('data.id');
        
        $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->putJson("/api/orders/{$orderId}", ['status' => 'confirmed']);
            
        $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->postJson("/api/orders/{$orderId}/pay", ['gateway_key' => 'paypal_express']);

        // Try Delete
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->deleteJson("/api/orders/{$orderId}");
            
        $response->assertStatus(400) // Expect 400 Bad Request
             ->assertJson(['error' => 'Cannot delete order with associated payments.']);
    }
}
