<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory
 */
class BrandFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        $brand_name = Str::ucfirst(Str::words(preg_replace('/[^A-Za-z]/', ' ', strtolower(fake()->company)), 1, ''));

        return [
            'tenant_id' => rand(1,3),
            'name' => $brand_name,
        ];
    }
}
