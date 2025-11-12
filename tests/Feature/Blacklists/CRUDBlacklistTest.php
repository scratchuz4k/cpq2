<?php

use App\Models\General\Blacklist;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->artisan('db:seed');
    setTenantForPermissions();
    $this->user = User::factory()->create();
    $this->user->tenants()->attach(Tenant::find(1));
    $this->user->assignRole('Company Admin');

    Auth::login($this->user);
    $this->actingAs($this->user);
});

it('Blacklists CRUD', function () {
    $data = [
        'productName' => 'teste',
        'providerName' => null,
        'connectivitySupplierGroup' => null,
        'bearer' => [
            'operator' => null,
            'value' => null,
            'unit' => null,
        ],
        'speed' => [
            'operator' => null,
            'value' => null,
            'unit' => null,
        ],
        'contractTermMonths' => null,
        'is_active' => true,
    ];

    $response = $this->post('blacklists', $data);
    $response->assertRedirect();

    expect(Blacklist::count())->toBe(1);

    // Update
    $blacklist = Blacklist::first();
    $data['productName'] = 'teste2';

    $response = $this->put('blacklists/'.$blacklist->id, $data);
    $response->assertRedirect();

    $blacklist->refresh();
    $this->assertEquals($blacklist->productName, 'teste2');

    $response = $this->delete('api/blacklists/'.$blacklist->id);
    $response->assertSuccessful();
    expect(Blacklist::count())->toBe(0);
});
