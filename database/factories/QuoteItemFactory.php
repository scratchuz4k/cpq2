<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\Quote;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory
 */
class QuoteItemFactory extends Factory
{
    /**
     * Define the model"s default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'tenant_id' => 1,
            'name' => 'example',
            'quantity' => 1,
            'price_plan_id' => 1,
            'product_id' => Product::factory()->create(),
            'quote_id' => Quote::factory()->create(),
        ];
    }
}
