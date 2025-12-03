<?php

namespace Database\Factories;

use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory
 */
class CategoryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        $word = $this->faker->unique()->word(1);

        return [
            'code' => $word,
            'name' => ucfirst($word),
            'tenant_id' => Tenant::where('name', 'CloudMarket')->value('id'),
        ];
    }
}
