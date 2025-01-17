<?php

namespace Database\Factories;

use App\Models\Account;
use App\Models\Tenant;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory
 */
class QuoteFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'tenant_id' => 1,
            'account_id' => Account::factory()->create(),
            'user_id' => 1,
            'name' => 'example order',
        ];
    }
}
