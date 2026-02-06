<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ValidationTest extends TestCase
{
    use RefreshDatabase;

    // --- Authentication ---

    public function test_register_requires_all_fields()
    {
        $response = $this->postJson('/api/register', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'email', 'password']);
    }

    public function test_register_requires_valid_email()
    {
        $response = $this->postJson('/api/register', [
            'name' => 'John',
            'email' => 'not-an-email',
            'password' => 'password123'
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_register_requires_unique_email()
    {
        User::factory()->create(['email' => 'duplicate@example.com']);

        $response = $this->postJson('/api/register', [
            'name' => 'John',
            'email' => 'duplicate@example.com',
            'password' => 'password123'
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_register_password_min_length()
    {
        $response = $this->postJson('/api/register', [
            'name' => 'John',
            'email' => 'valid@example.com',
            'password' => 'short' // less than 8
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['password']);
    }

    // --- Orders ---

    public function test_create_order_requires_items()
    {
        $user = User::factory()->create();
        $token = auth('api')->login($user);

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->postJson('/api/orders', [
                'items' => []
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['items']);
    }

    public function test_create_order_items_structure_validation()
    {
        $user = User::factory()->create();
        $token = auth('api')->login($user);

        // Missing product_name
        $response = $this->withHeader('Authorization', "Bearer $token")
            ->postJson('/api/orders', [
                'items' => [
                    ['quantity' => 1, 'unit_price' => 10] 
                ]
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['items.0.product_name']);

        // Invalid quantity (string instead of integer or negative)
        $response2 = $this->withHeader('Authorization', "Bearer $token")
            ->postJson('/api/orders', [
                'items' => [
                    ['product_name' => 'Foo', 'quantity' => 'string', 'unit_price' => 10]
                ]
            ]);
        $response2->assertStatus(422)
            ->assertJsonValidationErrors(['items.0.quantity']);
            
        // Negative unit price
        $response3 = $this->withHeader('Authorization', "Bearer $token")
            ->postJson('/api/orders', [
                'items' => [
                    ['product_name' => 'Foo', 'quantity' => 1, 'unit_price' => -10]
                ]
            ]);
        $response3->assertStatus(422)
             ->assertJsonValidationErrors(['items.0.unit_price']);
    }

    public function test_create_order_currency_validation()
    {
        $user = User::factory()->create();
        $token = auth('api')->login($user);

        // Currency too long
        $response = $this->withHeader('Authorization', "Bearer $token")
            ->postJson('/api/orders', [
                'items' => [['product_name' => 'A', 'quantity' => 1, 'unit_price' => 10]],
                'currency' => 'USDD'
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['currency']);
    }
    
    // --- Payments ---
    
    public function test_payment_requires_valid_gateway_key()
    {
        // Setup order
        $user = User::factory()->create();
        $token = auth('api')->login($user);
        
        $order = \App\Models\Order::factory()->for($user)->create(['status' => \App\Enums\OrderStatus::CONFIRMED]);
        
        // Invalid key
        $response = $this->withHeader('Authorization', "Bearer $token")
            ->postJson("/api/orders/{$order->id}/pay", [
                'gateway_key' => 'invalid_key_xyz'
            ]);
            
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['gateway_key']);
    }
}
