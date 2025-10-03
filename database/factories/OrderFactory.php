<?php

namespace Database\Factories;

use App\Models\Customer;
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
            'customer_id' => Customer::factory(), // Create a customer if not provided
            'status' => 'draft', // default status
            'total' => 0,        // can be updated later after adding lines
            'order_number' => 'ORD-' . strtoupper($this->faker->bothify('########')),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
