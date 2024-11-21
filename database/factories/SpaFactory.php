<?php

namespace Database\Factories;

use App\Models\Tenant;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory
 */
class SpaFactory extends Factory
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
            'supplier_id' => rand(1,10),
            'merchant_id' => rand(1,2) == 1 ? 1 : null,
            'account_id' => rand(1,2) == 1 ? 1 : null,
            'name' => fake()->streetName . ' SPA',
        ];
    }
}
