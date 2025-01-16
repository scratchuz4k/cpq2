<?php

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

function resolve_request($entity, $method)
{
    switch ($method) {
        case 'index':
            return ["method" => "get", "url" => $entity];
        case 'show':
            return ["method" => "get", "url" => "$entity/1"];
        case 'create':
            return ["method" => "get", "url" => "$entity/create"];
        case 'store':
            return ["method" => "post", "url" => "$entity", "content" =>  []];
        case 'edit':
            return ["method" => "get", "url" => "$entity/1/edit"];
        case 'update':
            return ["method" => "put", "url" => "$entity/1", "content" =>  []];
        case 'destroy':
            return ["method" => "delete", "url" => "$entity/1"];
    }
}

beforeEach(function () {
    $this->artisan('db:seed');
    $this->user = User::factory()->create();
    $this->user->tenants()->attach(Tenant::find(1));
    $this->user->assignRole('Order Manager');
    $this->account = Account::factory()->create();

    Auth::login($this->user);
    $this->actingAs($this->user);
});

it('Run Permissions for Order Manager', function () {
    $product = Product::factory()->create();
    $price = Price::factory()->state(["product_id" => $product->id])->create();
    $brand = Brand::factory()->create();
    $supplier = Supplier::factory()->create();
    $spa = Spa::factory()->create();
    $token = Token::factory()->create();
    $quote = Quote::factory()->create();
    $quoteItem = QuoteItem::factory()->state(["quote_id" => $quote->id, "product_id" => $product->id])->create();


    $permissions = [
        // 'order' => ['index' => true, 'show' => true, 'create' => true, 'store' => true, 'edit' => true, 'update' => true, 'destroy' => true],
        // 'order_item' =>  ['index' => true, 'show' => true, 'create' => true, 'store' => true, 'edit' => true, 'update' => true, 'destroy' => true],
        // 'merchant' => ['index' => false, 'show' => false, 'create' => false, 'store' => false, 'edit' => false, 'update' => false, 'destroy' => false],
        // 'contact' => ['index' => true, 'show' => true, 'create' => false, 'store' => false, 'edit' => false, 'update' => false, 'destroy' => false],
        // 'regions' => ['index' => false, 'show' => false, 'create' => false, 'store' => false, 'edit' => false, 'update' => false, 'destroy' => false],
        'tenant' => ['index' => false, 'show' => false, 'create' => false, 'store' => false, 'edit' => false, 'update' => false, 'destroy' => false],
        'api/accounts' => ['index' => true, 'show' => true, 'create' => false, 'store' => false, 'edit' => false, 'update' => false, 'destroy' => false],
        'api/prices' => ['index' => false, 'show' => false, 'create' => false, 'store' => false, 'edit' => false, 'update' => false, 'destroy' => false],
        'products' => ['index' => true, 'show' => true, 'create' => false, 'store' => false, 'edit' => false, 'update' => false, 'destroy' => false],
        'brands' => ['index' => true, 'show' => true, 'create' => false, 'store' => false, 'edit' => false, 'update' => false, 'destroy' => false],
        'suppliers' => ['index' => true, 'show' => true, 'create' => false, 'store' => false, 'edit' => false, 'update' => false, 'destroy' => false],
        'spas' => ['index' => false, 'show' => false, 'create' => false, 'store' => false, 'edit' => false, 'update' => false, 'destroy' => false],
        'users' => ['index' => false, 'show' => false, 'create' => false, 'store' => false, 'edit' => false, 'update' => false, 'destroy' => false],
        'tokens' => ['index' => false, 'show' => false, 'create' => false, 'store' => false, 'edit' => false, 'update' => false, 'destroy' => false],
        'quotes' => ['index' => false, 'show' => false, 'create' => false, 'store' => false, 'edit' => false, 'update' => false, 'destroy' => false],
        'api/quote-items' => ['index' => false, 'show' => false, 'create' => false, 'store' => false, 'edit' => false, 'update' => false, 'destroy' => false],
        'solution-types' => ['index' => false, 'show' => false, 'create' => false, 'store' => false, 'edit' => false, 'update' => false, 'destroy' => false],
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
                    $response->assertStatus(200);
                } else {
                    $response->assertStatus(403);
                }
            } catch (Exception $exception) {
                $message = $exception->getMessage();
                throw new Exception("Permissions validation errors on Order Manager: [Entity: $entity, Method: $key]\n\nContext:\n$message");
            }
        }
    }
});
