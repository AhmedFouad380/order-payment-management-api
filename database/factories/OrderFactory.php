<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Order>
 */
class OrderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'uuid' => \Illuminate\Support\Str::uuid(),
            'status' => \App\Enums\OrderStatus::PENDING, // Default, can be overridden
            'currency' => 'USD',
            'total_amount' => $this->faker->randomFloat(2, 50, 500),
            'customer_details' => [
                'phone' => $this->faker->phoneNumber(),
                'address' => $this->faker->address(),
            ],
        ];
    }
}
