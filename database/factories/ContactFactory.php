<?php

namespace Database\Factories;

use App\Models\Contact;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Contact>
 */
class ContactFactory extends Factory
{
    protected $model = Contact::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'account_id' => \App\Models\Account::factory(),
            'primary_contact_name' => $this->faker->name,
            'email' => $this->faker->unique()->safeEmail,
            'address_1' => $this->faker->address,
            'city' => $this->faker->city,
            'postcode' => $this->faker->postcode,
        ];
    }
}
