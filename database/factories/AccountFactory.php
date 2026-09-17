<?php

namespace Database\Factories;

use App\Models\Contact;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory
 */
class AccountFactory extends Factory
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
            'tenant_id' => Tenant::where('name', 'CloudMarket')->value('id'),
            'reference' => strtoupper(substr($company_name, 0, 3)).fake()->randomNumber(5, true),
            'name' => $company_name,
            'is_active' => fake()->randomElement([0, 1]),
        ];
    }

    public function withContacts($count = 5)
    {
        return $this->has(Contact::factory()->count($count), 'contacts');
    }
}
