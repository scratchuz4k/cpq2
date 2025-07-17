<?php

use App\Models\Account;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->artisan('db:seed');
    $this->user = User::factory()->create();
    $this->tenant = Tenant::find(1);
    $this->user->tenants()->attach($this->tenant);
    $this->user->assignRole('Company Admin');

    Auth::login($this->user);
    $this->actingAs($this->user);
});

it('Create active Account ', function () {
    $data = [
        'name' => fake()->name,
        'reference' => 'ref',
        'is_active' => true,
    ];

    $response = $this->post('accounts', $data);
    $response->assertRedirect();
    expect(Account::count())->toBe(1);

    $account = Account::find(1);

    //Check accounts index
    $response = $this->get('api/accounts/');
    $accounts = $response->json('data');

    expect(count($accounts))->toBe(1);
    expect($accounts[0]['is_active'])->toBe(true);

    $data = [
        'is_active' => false,
    ];

    $response = $this->put('accounts/' . $account->id, $data);
    $response->assertRedirect();
    $account->refresh();
    expect($account->is_active)->toBe(false);

    $string = urlencode(json_encode([
        [
            'column' => 'is_active',
            'operator' => 'equals',
            'value' => 1
        ]
    ]));

    $response = $this->get('api/accounts?filters=' . $string);
    $accounts = $response->json('data');

    expect(count($accounts))->toBe(0);

    $response = $this->delete('api/accounts/' . $account->id);
    $response->assertSuccessful();
    expect(Account::count())->toBe(0);
});

