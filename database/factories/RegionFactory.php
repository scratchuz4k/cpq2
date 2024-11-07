<?php

namespace Database\Factories;

use App\Models\Tenant;
use App\Models\Merchant;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory
 */
class RegionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        $areas = ['', 'North', 'South', 'East', 'West'];
        $area = rand(0, count($areas) - 1);

        return [
            'tenant_id' => Tenant::where("name", "CloudMarket")->value('id'),
            'merchant_id' => Merchant::factory()->create(),
            'user_id' => 1,
            'name' => trim(fake()->city . ' ' . $areas[$area]),
        ];
    }
}
