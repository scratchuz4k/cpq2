<?php

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->artisan('db:seed');

    $this->user = User::factory()->create();
    $this->user->tenants()->attach(Tenant::find(1));
    $this->user->assignRole('Support Agent');

    $second_tenant = Tenant::factory()->create();
    $this->second_tenant_user = User::factory()->create([]);
    $this->second_tenant_user->tenants()->attach($second_tenant);
    $this->second_tenant_user->assignRole('Support Agent');
});

it('Create Token for first Tenant', function () {
    Auth::login($this->user);
    $this->actingAs($this->user);

    $data = [
        'name' => 'test',
        'user_id' => $this->user->id,
        'is_active' => true,
    ];

    $response = $this->post('/tokens', $data);
    $response->assertRedirect();

    $response = $this->get('api/tokens');
    expect(count($this->get('api/tokens')->json()['data']))->toBe(1);
});

it('Create Token for second Tenant', function () {
    Auth::login($this->second_tenant_user);
    $this->actingAs($this->second_tenant_user);

    $data = [
        'name' => 'test',
        'user_id' => $this->second_tenant_user->id,
        'is_active' => true,
    ];

    $response = $this->post('/tokens', $data);
    $response->assertRedirect();

    $response = $this->get('/tokens');
    expect(count($this->get('api/tokens')->json()['data']))->toBe(1);
});
