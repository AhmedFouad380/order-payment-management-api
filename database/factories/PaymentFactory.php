<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Payment>
 */
class PaymentFactory extends Factory
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
            'transaction_id' => $this->faker->uuid(),
            'amount' => $this->faker->randomFloat(2, 50, 500),
            'gateway_key' => $this->faker->randomElement(['paypal_express', 'stripe_cc']),
            'status' => \App\Enums\PaymentStatus::SUCCESSFUL,
            'response_data' => ['mock' => 'data'],
        ];
    }
}
