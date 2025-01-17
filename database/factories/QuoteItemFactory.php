<?php

namespace Database\Factories;

use App\Models\Quote;
use App\Models\Tenant;
use App\Models\Product;
use Illuminate\Support\Str;
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
            "tenant_id" => 1,
            "name" => "example",
            "quantity" => 1,
            "price_plan_id" => 1
        ];
    }
}
