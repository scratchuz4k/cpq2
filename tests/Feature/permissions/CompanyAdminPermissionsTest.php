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

    $dummyContent = [
        'tenant' => Tenant::factory()->make()->toArray(),
        'products' => Product::factory()->make()->toArray(),
        'brands' => Brand::factory()->make()->toArray(),
        'suppliers' => Supplier::factory()->make()->toArray(),
        'spas' => Spa::factory()->make()->toArray(),
        'users' => User::factory()->make()->toArray(),
        'tokens' => Token::factory()->make()->toArray(),
        'quotes' => Quote::factory()->make()->toArray(),
        'solution-types' => [],
        'apis' => Api::factory()->make()->toArray(),
        'api/accounts' => Account::factory()->make()->toArray(),
        'api/prices' => Price::factory()->make()->toArray(),
        'api/quote-items' => QuoteItem::factory()->make()->toArray(),
    ];

    $permissions = [
        'tenants' => ['index' => true,  'create' => true, 'store' => true, 'show' => true, 'edit' => true, 'update' => true, 'destroy' => true],
        'products' => ['index' => true,  'create' => true, 'store' => true, 'show' => true, 'edit' => true, 'update' => true, 'destroy' => true],
        'brands' => ['index' => true,  'create' => true, 'store' => true, 'show' => true, 'edit' => true, 'update' => true, 'destroy' => true],
        'suppliers' => ['index' => true,  'create' => true, 'store' => true, 'show' => true, 'edit' => true, 'update' => true, 'destroy' => true],
        'spas' => ['index' => true,  'create' => true, 'store' => true, 'show' => true, 'edit' => true, 'update' => true, 'destroy' => true],
        'users' => ['index' => true,  'create' => true, 'store' => true, 'show' => true, 'edit' => true, 'update' => true, 'destroy' => true],
        'tokens' => ['index' => false,  'create' => false, 'store' => false, 'show' => false, 'edit' => false, 'update' => false, 'destroy' => false],
        'quotes' => ['index' => true,  'create' => true, 'store' => true, 'show' => true, 'edit' => true, 'update' => true, 'destroy' => true],
        'solution-types' => ['index' => true,  'create' => false, 'store' => false, 'show' => true, 'edit' => true, 'update' => true, 'destroy' => false],
        'apis' => ['index' => true,  'create' => true, 'store' => true, 'show' => true, 'edit' => true, 'update' => true, 'destroy' => true],
        'api/accounts' => ['index' => true,  'create' => true, 'store' => true, 'show' => true, 'edit' => true, 'update' => true, 'destroy' => true],
        'api/prices' => ['index' => true,  'create' => true, 'store' => true, 'show' => true, 'edit' => true, 'update' => true, 'destroy' => true],
        'api/quote-items' => ['index' => true,  'create' => true, 'store' => true, 'show' => true, 'edit' => true, 'update' => true, 'destroy' => true],
    ];

    foreach ($permissions as $entity => $routes) {
        foreach ($routes as $key => $hasPermission) {

            $append = '';
            if (str_starts_with($entity, 'api')) {
                $append = 'Json';
                if (in_array($key, ['edit', 'create'])) {
                    continue;
                }
            }

            try {
                $content = array_key_exists($entity, $dummyContent) ? $dummyContent[$entity] : [];
                $request = resolve_request($entity, $key, $content);
                $response =
                    array_key_exists('content', $request)
                        ? $this->{$request['method'].$append}($request['url'], $content)
                        : $this->{$request['method'].$append}($request['url']);
                if ($hasPermission) {
                    expect(in_array($response->status(), [200, 201, 204, 302]))->toBeTrue();
                } else {
                    $response->isForbidden();
                }

                if ($key == 'store' && $permissions[$entity]['index']) {
                    $url = str_replace('api/', '', $entity);
                    $res = $this->get("api/$url", ['order' => 'desc']);
                    $dummyContent[$entity] = $res->json()['data'][0];
                }

            } catch (Exception $exception) {
                $message = $exception->getMessage();
                $error = json_encode($response->json());

                throw new Exception("Permissions validation errors on Company Admin: [Entity: $entity, Method: $key]\n\nContext:\n$message\nMessage: $error");
            }
        }
    }
});
