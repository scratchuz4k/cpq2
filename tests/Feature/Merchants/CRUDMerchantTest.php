<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Tenant;
use App\Models\Merchant;
use Illuminate\Support\Facades\Auth;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->artisan('db:seed');
    $this->user = User::factory()->create();
    $this->user->tenants()->attach(Tenant::find(1));
    $this->user->assignRole('Company Admin');
    $this->merchant = 'Merchant ' . hash('md5', date("Y-m-d H:i:s"));

    Auth::login($this->user);
    $this->actingAs($this->user);
});

it('Merchant CRUD', function () {
    //Create Merchant
    $data = [
        'name' => $this->merchant,
    ];

    $response = $this->post('merchants', $data);
    $response->assertRedirect();
    expect(Merchant::count())->toBe(1);

    //Update Merchant
    $merchant = Merchant::find(1);

    $data = [
        'name' => 'Updated ' . $this->merchant,
    ];

    $response = $this->put('merchants/' . $merchant->id, $data);
    $response->assertRedirect();

    $merchant->refresh();
    $this->assertEquals($merchant->name, 'Updated ' . $this->merchant);

    //Delete Merchant
    $response = $this->delete('api/merchants/' . $merchant->id);
    $response->assertOk();
    expect(Merchant::count())->toBe(0);
});
