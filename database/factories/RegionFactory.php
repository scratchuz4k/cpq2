<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

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
            'tenant_id' => rand(1,3),
            'merchant_id' => 1,
            'user_id' => rand(2,4),
            'name' => trim(fake()->city . ' ' . $areas[$area]),
        ];
    }
}
