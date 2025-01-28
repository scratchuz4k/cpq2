<?php

namespace Database\Factories;

use App\Enums\CloudMarketSuppliersEnum;
use App\Models\Region;
use App\Models\Tenant;
use App\Models\Contact;
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
        $company_name = fake()->company();

        return [
            'code' => CloudMarketSuppliersEnum::ITS,
            'name' => fake()->name(),
            'auth_type' => 'basic',
            'username' => fake()->username(),
            'password' => fake()->password(),
            'tenant_id' => Tenant::where("name", "CloudMarket")->value('id')
        ];
    }
}
