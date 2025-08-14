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

it('CRUD if setting is disable', function () {
    $data = [
        'name' => 'name',
        'reference' => 'ref',
        'is_active' => true,
    ];

    $response = $this->post('accounts', $data);
    $response->assertRedirect();
    expect(Account::count())->toBe(1);

    // Update Api
    $account = Account::find(1);

    $data = [
        'name' => 'updated_name',
        'reference' => 'ref',
    ];

    $response = $this->put('accounts/'.$account->id, $data);
    $response->assertRedirect();

    $account->refresh();
    $this->assertEquals($account->name, $data['name']);

    $response = $this->delete('api/accounts/'.$account->id);
    $response->assertSuccessful();
    expect(Account::count())->toBe(0);
});

it('CRUD if setting is enabled', function () {
    $account = Account::factory()->create();
    $confs = [
        'restrict_account_management_is_active' => 1,
    ];

    $this->tenant->syncConfigurations($confs);

    $data = [
        'name' => 'name',
        'reference' => 'ref',
    ];

    $response = $this->post('accounts', $data);
    $response->assertForbidden();
    expect(Account::count())->toBe(1);

    // Update Api
    $new_data = [
        'name' => 'updated_name',
        'reference' => 'ref',
    ];
    $response = $this->put('accounts/'.$account->id, $new_data);
    $response->assertForbidden();

    $this->assertEquals($account->name, $account->name);

    $response = $this->delete('api/accounts/'.$account->id);
    $response->assertForbidden();
    expect(Account::count())->toBe(1);
});

it('Check API access to accounts if setting is enabled for user with token permission', function () {
    $confs = [
        'restrict_account_management_is_active' => 1,
    ];

    $this->tenant->syncConfigurations($confs);
    $this->tenant->refresh();

    $user = User::factory()->create();
    $user->tenants()->attach($this->tenant);
    $user->assignRole('Support Agent');

    Auth::guard('web')->logout();
    Auth::guard('web')->login($user);

    $data = [
        'name' => 'name',
        'reference' => 'ref',
    ];

    $response = $this->postJson('api/accounts', $data, [
        'Accept' => 'application/json',
    ]);
    $response->assertRedirect();
    expect(Account::count())->toBe(1);

    // Update Api
    $account = Account::find(1);

    $data = [
        'name' => 'updated_name',
        'reference' => 'ref',
    ];

    $response = $this->putJson('api/accounts/'.$account->id, $data);
    $response->assertSuccessful();

    $account->refresh();
    $this->assertEquals($account->name, $data['name']);

    $response = $this->deleteJson('api/accounts/'.$account->id);
    $response->assertSuccessful();
    expect(Account::count())->toBe(0);
});
