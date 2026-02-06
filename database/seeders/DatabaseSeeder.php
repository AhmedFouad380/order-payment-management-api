<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Seed Gateways
        $this->call(PaymentGatewaySeeder::class);

        // 2. Create Test User
        $user = \App\Models\User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password', // Will be hashed by Mutator or factory? Factory usually sets 'password'. Default has 'password' => static::$password ??= Hash::make('password') in Laravel 11 factory.
        ]);

        // 3. Create Random Orders for this user
        \App\Models\Order::factory(5)
            ->for($user)
            ->has(\App\Models\OrderItem::factory()->count(3), 'items') // Create 3 items per order
            ->create()
            ->each(function ($order) {
                // Update total amount based on items
                $order->update([
                    'total_amount' => $order->items->sum('subtotal')
                ]);
            });
        
        // 4. Create dummy users with orders
        \App\Models\User::factory(5)
            ->has(
                \App\Models\Order::factory()
                    ->count(2)
                    ->has(\App\Models\OrderItem::factory()->count(2), 'items')
                    ->state(function (array $attributes, \App\Models\User $user) {
                        return ['user_id' => $user->id];
                    })
            )
            ->create()
             ->each(function ($user) {
                $user->orders->each(function ($order) {
                     $order->update([
                        'total_amount' => $order->items->sum('subtotal'),
                        'status' => \App\Enums\OrderStatus::CONFIRMED // Confirm orders so we can pay them
                    ]);

                    // Create Payment for this order
                    \App\Models\Payment::factory()->create([
                        'order_id' => $order->id,
                        'amount' => $order->total_amount,
                        'gateway_key' => 'paypal_express'
                    ]);
                });
            });
    }
}
