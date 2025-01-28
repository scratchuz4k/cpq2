<?php

use App\Models\Api;
use App\Models\Spa;
use App\Models\User;
use App\Models\Brand;
use App\Models\Price;
use App\Models\Quote;
use App\Models\Token;
use App\Models\Tenant;
use App\Models\Account;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\QuoteItem;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->artisan('db:seed');
    $this->user = User::factory()->create();
    $this->user->tenants()->attach(Tenant::find(1));
    $this->user->assignRole('Company Admin');
    $this->account = Account::factory()->create();

    Auth::login($this->user);
    $this->actingAs($this->user);
});

it('Run Permissions for Company Admin', function () {
    $product = Product::factory()->create();
    $price = Price::factory()->state(["product_id" => $product->id])->create();
    $brand = Brand::factory()->create();
    $supplier = Supplier::factory()->create();
    $spa = Spa::factory()->create();
    $token = Token::factory()->create();
    $quote = Quote::factory()->create();
    $quoteItem = QuoteItem::factory()->state(["quote_id" => $quote->id, "product_id" => $product->id])->create();
    $api = Api::factory()->create(['tenant_id' => $this->account->tenant_id]);

    $permissions = [
        'tenant' => ['index' => true, 'show' => true, 'create' => true, 'store' => true, 'edit' => true, 'update' => true, 'destroy' => true],
        'api/accounts' => ['index' => true, 'show' => true, 'create' => true, 'store' => true, 'edit' => true, 'update' => true, 'destroy' => true],
        'api/prices' => ['index' => true, 'show' => true, 'create' => true, 'store' => true, 'edit' => true, 'update' => true, 'destroy' => true],
        'products' => ['index' => true, 'show' => true, 'create' => true, 'store' => true, 'edit' => true, 'update' => true, 'destroy' => true],
        'brands' => ['index' => true, 'show' => true, 'create' => true, 'store' => true, 'edit' => true, 'update' => true, 'destroy' => true],
        'suppliers' => ['index' => true, 'show' => true, 'create' => true, 'store' => true, 'edit' => true, 'update' => true, 'destroy' => true],
        'spas' => ['index' => true, 'show' => true, 'create' => true, 'store' => true, 'edit' => true, 'update' => true, 'destroy' => true],
        'users' => ['index' => true, 'show' => true, 'create' => true, 'store' => true, 'edit' => true, 'update' => true, 'destroy' => true],
        'tokens' => ['index' => false, 'show' => false, 'create' => false, 'store' => false, 'edit' => false, 'update' => false, 'destroy' => false],
        'quotes' => ['index' => true, 'show' => true, 'create' => true, 'store' => true, 'edit' => true, 'update' => true, 'destroy' => true],
        'api/quote-items' => ['index' => true, 'show' => true, 'create' => true, 'store' => true, 'edit' => true, 'update' => true, 'destroy' => true],
        'solution-types' => ['index' => true, 'show' => true, 'create' => true, 'store' => true, 'edit' => true, 'update' => true, 'destroy' => true],
        'apis' => ['index' => true, 'show' => true, 'create' => true, 'store' => true, 'edit' => true, 'update' => true, 'destroy' => true],
    ];

    foreach ($permissions as $entity => $routes) {
        foreach ($routes as $key => $hasPermission) {
            if (str_starts_with($entity, "api") && in_array($key, ["edit", "create"])) {
                continue;
            }
            try {
                $request = resolve_request($entity, $key);
                $response =
                    array_key_exists("content", $request)
                        ? $this->{$request["method"]}($request["url"], [])
                        : $this->{$request["method"]}($request["url"]);
                if ($hasPermission) {
                    expect(in_array($response->status(), [200, 204, 302]))->toBeTrue();
                } else {
                    $response->assertStatus(403);
                }
            } catch (Exception $exception) {
                $message = $exception->getMessage();
                throw new Exception("Permissions validation errors on Company Admin: [Entity: $entity, Method: $key]\n\nContext:\n$message");
            }
        }
    }
});
