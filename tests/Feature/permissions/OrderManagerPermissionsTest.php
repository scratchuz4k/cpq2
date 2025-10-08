<?php

use App\Models\Account;
use App\Models\Brand;
use App\Models\Supplier;
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
    $this->user->assignRole('Order Manager');
    $this->account = Account::factory()->create();
    Brand::factory()->create();
    Supplier::factory()->create();

    Auth::login($this->user);
    $this->actingAs($this->user);
});

it('Run Permissions for Order Manager', function () {
    $modelsData = generateModelsData();

    $permissions = [
        'tenant' => ['index' => false, 'create' => false, 'show' => false, 'store' => false, 'edit' => false, 'update' => false, 'destroy' => false],
        'api/accounts' => ['index' => true, 'create' => false, 'show' => true, 'store' => false, 'edit' => false, 'update' => false, 'destroy' => false],
        'api/prices' => ['index' => false, 'create' => false, 'show' => false, 'store' => false, 'edit' => false, 'update' => false, 'destroy' => false],
        'products' => ['index' => true, 'create' => false, 'show' => true, 'store' => false, 'edit' => false, 'update' => false, 'destroy' => false],
        'brands' => ['index' => true, 'create' => false, 'show' => true, 'store' => false, 'edit' => false, 'update' => false, 'destroy' => false],
        'suppliers' => ['index' => true, 'create' => false, 'show' => true, 'store' => false, 'edit' => false, 'update' => false, 'destroy' => false],
        'spas' => ['index' => false, 'create' => false, 'show' => false, 'store' => false, 'edit' => false, 'update' => false, 'destroy' => false],
        'users' => ['index' => false, 'create' => false, 'show' => false, 'store' => false, 'edit' => false, 'update' => false, 'destroy' => false],
        'tokens' => ['index' => false, 'create' => false, 'show' => false, 'store' => false, 'edit' => false, 'update' => false, 'destroy' => false],
        'quotes' => ['index' => false, 'create' => false, 'show' => false, 'store' => false, 'edit' => false, 'update' => false, 'destroy' => false],
        'api/quote-items' => ['index' => false, 'create' => false, 'show' => false, 'store' => false, 'edit' => false, 'update' => false, 'destroy' => false],
        'solution-types' => ['index' => false, 'create' => false, 'show' => false, 'store' => false, 'edit' => false, 'update' => false, 'destroy' => false],
    ];

    runPermissionTest($permissions, $modelsData);
});
