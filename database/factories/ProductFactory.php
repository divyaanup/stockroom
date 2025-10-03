<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->word,
            'sku' => $this->faker->unique()->bothify('SKU-##??'),
            'price' => $this->faker->randomFloat(2, 1, 2000),
            'stock_on_hand' => $this->faker->numberBetween(1,200),
            'reorder_threshold' => $this->faker->numberBetween(1,10),
            'status' => 'active',
            'tags' => json_encode([$this->faker->word, $this->faker->word]),
        ];
    }
}
