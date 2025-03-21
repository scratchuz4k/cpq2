<?php

use App\Models\Account;
use App\Models\Api;
use App\Models\Brand;
use App\Models\Price;
use App\Models\Product;
use App\Models\Quote;
use App\Models\QuoteItem;
use App\Models\Spa;
use App\Models\Supplier;
use App\Models\Tenant;
use App\Models\Token;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
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

it('Run Permissions for Company Admin', function () {
    $modelsData = generateModelsData();
    $permissions = [
        'tenants' => ['index' => true,  'create' => true, 'store' => true, 'show' => true, 'edit' => true, 'update' => true, 'destroy' => true],
        'products' => ['index' => true,  'create' => true, 'store' => true, 'show' => true, 'edit' => true, 'update' => true, 'destroy' => true],
        'brands' => ['index' => true,  'create' => true, 'store' => true, 'show' => true, 'edit' => true, 'update' => true, 'destroy' => true],
        'suppliers' => ['index' => true,  'create' => true, 'store' => true, 'show' => true, 'edit' => true, 'update' => true, 'destroy' => true],
        'spas' => ['index' => true,  'create' => true, 'store' => true, 'show' => true, 'edit' => true, 'update' => true, 'destroy' => true],
        'users' => ['index' => true,  'create' => true, 'store' => true, 'show' => true, 'edit' => true, 'update' => true, 'destroy' => true],
        'tokens' => ['index' => false,  'create' => false, 'store' => false, 'show' => false, 'edit' => false, 'update' => false, 'destroy' => false],
        'quotes' => ['index' => true,  'create' => true, 'store' => true, 'show' => true, 'edit' => true, 'update' => true, 'destroy' => true],
        'solution-types' => ['index' => true,  'create' => true, 'store' => true, 'show' => true, 'edit' => true, 'update' => true, 'destroy' => true],
        'apis' => ['index' => true,  'create' => true, 'store' => true, 'show' => true, 'edit' => true, 'update' => true, 'destroy' => true],
        'api/accounts' => ['index' => true,  'create' => true, 'store' => true, 'show' => true, 'edit' => true, 'update' => true, 'destroy' => true],
        'api/prices' => ['index' => true,  'create' => true, 'store' => true, 'show' => true, 'edit' => true, 'update' => true, 'destroy' => true],
        'api/quote-items' => ['index' => true,  'create' => true, 'store' => true, 'show' => true, 'edit' => true, 'update' => true, 'destroy' => true],
    ];

    runPermissionTest($permissions, $modelsData);
});
