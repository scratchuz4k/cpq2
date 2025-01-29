<?php

namespace Database\Factories;

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
            'rule_id' => 1,
            'field_1' => 1,
            'field_2' => 0,
            'operation_id' => 1,
            'value' => 1,
            'percentage' => 1,
        ];
    }
}
