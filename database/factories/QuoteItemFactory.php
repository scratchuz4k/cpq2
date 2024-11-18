<?php

namespace Database\Factories;

use App\Models\Tenant;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory
 */
class QuoteItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'tenant_id' => Tenant::where("name", "CloudMarket")->value('id'),
            'order_id' => 1,
            'product_id' => rand(1,5) == 1 ? null : 1,
            'name' => 'example item',
        ];
    }
}
