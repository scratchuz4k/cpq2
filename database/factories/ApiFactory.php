<?php

namespace Database\Factories;

use App\Enums\CloudMarketSuppliersEnum;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory
 */
class ApiFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {

        return [
            'code' => CloudMarketSuppliersEnum::ITS,
            'name' => fake()->name(),
            'auth_type' => 'basic',
            'username' => fake()->username(),
            'password' => fake()->password(),
            'tenant_id' => 1,
        ];
    }
}
