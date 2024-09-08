<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory
 */
class TenantFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        $username = fake()->userName;
        $username = trim(preg_replace('/[^a-z]+/', ' ', $username));
        $tenant_domain = Str::words($username, 1, '');
        $tenant_name = ucfirst($tenant_domain);
        
        return [
            'name' => $tenant_name,
            'domain' => $tenant_domain . '.example.com',
            'database' => $tenant_domain,
        ];
    }
}
