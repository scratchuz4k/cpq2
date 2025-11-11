<?php

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->artisan('db:seed');
});

it('login screen can be rendered', function () {
    $response = $this->get('/login');

    $response->assertStatus(200);
});

it('users can authenticate using the login screen', function () {
    setTenantForPermissions();
    $user = User::factory()->create();
    $user->tenants()->attach(Tenant::find(1));
    $user->assignRole('Company Admin');

    $response = $this->post('/login', [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect(route('quotes.index', absolute: false));
});

it('users can not authenticate with invalid password', function () {
    $user = User::factory()->create();

    $this->post('/login', [
        'email' => $user->email,
        'password' => 'wrong-password',
    ]);

    $this->assertGuest();
});

it('users can logout', function () {
    setTenantForPermissions();
    $user = User::factory()->create();
    $user->tenants()->attach(Tenant::find(1));
    $user->assignRole('Company Admin');

    $response = $this->actingAs($user)->post('/logout');

    $this->assertGuest();
    $response->assertRedirect('/');
});
