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

    Auth::login($this->user);
    $this->actingAs($this->user);
});

it('Solution Type CRUD', function () {
    //Create Merchant
    $data = [
        'tenant_id' => 1,
        'solution_type_id' => 1,
        'configurations' => [
            'component' => null
        ]
    ];
    $response = $this->post('solution-types', $data);
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
