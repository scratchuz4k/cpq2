<?php

namespace Database\Factories;

use App\Models\Contact;
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
            'tenant_id' => rand(1,3),
            'region_id' => 1,
            'reference' => strtoupper(substr($company_name, 0, 3)) . fake()->randomNumber(5, true),
            'name' => $company_name,
        ];
    }

    public function withContacts($count = 5)
    {
        return $this->has(Contact::factory()->count($count), 'contacts');
    }
}
