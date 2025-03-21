<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Price;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory
 */
class TokenFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            "name" => "test",
            "token" => "",
            "tokenable_type" => User::class,
            "tokenable_id" => 1
        ];
    }
}
