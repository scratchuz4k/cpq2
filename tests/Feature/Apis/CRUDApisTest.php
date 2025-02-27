<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Tenant;
use App\Models\Api;
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

it('Apis CRUD', function () {
    $data = [
        'code' => 'its',
        'name' => 'Its',
        'auth_type' => 'basic',
        'username' => 'Test',
        'password' => 'Test',
        'client_id' => null,
        'token' => null,
        'is_active' => false
    ];

    $response = $this->post('apis', $data);
    $response->assertRedirect();
    expect(Api::count())->toBe(1)->and(Api::active()->count())->toBe(0);

    //Update Merchant
    $api = Api::find(1);

    $data = [
        'is_active' => true
    ];

    $response = $this->put('apis/' . $api->id, $data);
    $response->assertRedirect();

    $api->refresh();
    $this->assertTrue($api->is_active);

    //Delete Merchant
    $response = $this->delete('api/apis/' . $api->id);
    $response->assertOk();
    expect(Api::count())->toBe(0);
});
