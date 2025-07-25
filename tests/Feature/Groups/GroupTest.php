<?php

use App\Models\Account;
use App\Models\Group;
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
    $this->user->assignRole('Sales Manager');

    $this->account = Account::factory()->create();
    $this->group = Group::factory()->create();

    Auth::login($this->user);
    $this->actingAs($this->user);
});

it('Relate Account and User with Group, ', function () {
    // relate User
    $response = $this->put('api/users/'.$this->user->id, [
        'group_id' => $this->group->id,
    ]);
    $response->assertSuccessful();
    $this->user->refresh();
    expect($this->user->group()->count())->toBe(1);

    $response = $this->post('api/group-accounts/', [
        'account_id' => $this->account->id,
        'group_id' => $this->group->id,
    ]);
    $response->assertSuccessful();

    $this->group->refresh();
    expect($this->group->accounts()->count())->toBe(1);
});

it('Add user to Group and create new Account and User ', function () {
    $response = $this->put('api/users/'.$this->user->id, [
        'group_id' => $this->group->id,
    ]);
    $response->assertSuccessful();
    $this->user->refresh();
    expect($this->user->group()->count())->toBe(1);

    $response = $this->post('accounts', [
        'name' => 'name',
        'reference' => 'ref',
    ]);
    $response->assertRedirect();
    expect(Account::count())->toBe(2);

    $account = Account::find(1);

    $user_data = [
        'name' => 'Test User',
        'email' => 'testuser@test',
        'password' => 'password',
        'role_id' => 5,
        'parent_id' => 0,
    ];

    $response = $this->post('users', $user_data);
    $response->assertRedirect();

    $user = User::where('email', $user_data['email'])->first();
    expect($user->name)->toBe($user_data['name']);

    // Check relations
    expect($this->group->accounts()->count())->toBe(1);
    expect($user->group()->count())->toBe(1);
});
