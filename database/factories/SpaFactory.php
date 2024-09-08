<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

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
            'tenant_id' => rand(1,3),
            'supplier_id' => rand(1,10),
            'merchant_id' => rand(1,2) == 1 ? 1 : null,
            'account_id' => rand(1,2) == 1 ? 1 : null,
            'name' => fake()->streetName . ' SPA',
        ];
    }
}
