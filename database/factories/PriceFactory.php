<?php

namespace Database\Factories;

use App\Models\Price;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory
 */
class PriceFactory extends Factory
{
    protected $model = Price::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        $code = $this->faker->unique()->randomNumber;
        $net = $this->faker->randomFloat(2, 1, 1000);
        $cost = $this->faker->randomFloat(2, 1, 1000);

        return [
            'tenant_id' => 1,
            'code' => $code,
            'net' => $net,
            'cost' => $cost,
            'product_id' => Product::factory()->create(),
        ];
    }
}
