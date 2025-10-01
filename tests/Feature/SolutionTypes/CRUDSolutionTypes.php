<?php

use App\Models\Merchant;
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

it('Solution Type CRUD', function () {
    // Create Merchant
    $data = [
        'tenant_id' => 1,
        'solution_type_id' => 1,
        'configurations' => [
            'component' => null,
        ],
    ];
    $response = $this->post('solution-types', $data);
    $response->assertRedirect();

    expect($this->user->currentTenant->solutionTypes()->count())->toBe(1);

    // Update solution type
    $solutionType = $this->user->currentTenant->solutionTypes()->first();

    $data = [
        'configurations' => [
            'component' => 'Simple',
        ],
    ];

    $response = $this->put('solution-types/'.$solutionType->id, $data);
    $response->assertRedirect();

    $solutionType->refresh();

    // Delete solution type
    $response = $this->delete('api/solution-types/'.$solutionType->id);
    $response->assertSuccessful();
    expect($this->user->currentTenant->solutionTypes()->count())->toBe(0);
});
