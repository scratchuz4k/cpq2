<?php

namespace Database\Factories;

use App\Models\SolutionType;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory
 */
class StepFactory extends Factory
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
            'name' => ucfirst($word),
            'tenant_id' => Tenant::where('name', 'CloudMarket')->value('id'),
            'solution_type_id' => SolutionType::first()->id,
        ];
    }
}
