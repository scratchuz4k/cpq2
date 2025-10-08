<?php

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, that class is "PHPUnit\Framework\TestCase". Of course, you may
| need to change it using the "pest()" function to bind a different classes or traits.
|
*/

use App\Models\Account;
use App\Models\Api;
use App\Models\Brand;
use App\Models\Price;
use App\Models\Product;
use App\Models\Quote;
use App\Models\QuoteItem;
use App\Models\SolutionType;
use App\Models\Spa;
use App\Models\Supplier;
use App\Models\Tenant;
use App\Models\Token;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\PermissionRegistrar;

pest()->extend(Tests\TestCase::class)
    ->use(Illuminate\Foundation\Testing\RefreshDatabase::class)
    ->in('Feature');
pest()->extend(Tests\TestCase::class)
    ->use(Illuminate\Foundation\Testing\RefreshDatabase::class)
    ->in('Unit');

uses(RefreshDatabase::class)->in('Feature');

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
|
| When you're writing tests, you often need to check that values meet certain conditions. The
| "expect()" function gives you access to a set of "expectations" methods that you can use
| to assert different things. Of course, you may extend the Expectation API at any time.
|
*/

expect()->extend('toBeOne', fn () => $this->toBe(1));

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
|
| While Pest is very powerful out-of-the-box, you may have some testing code specific to your
| project that you don't want to repeat in every file. Here you can also expose helpers as
| global functions to help you to reduce the number of lines of code in your test files.
|
*/

function createUser()
{
    $tenant = Tenant::updateOrCreate(['name' => 'testing'], ['domain' => 'testing', 'database' => 'testing']);
    $user = User::factory()->create();
    $tenant->users()->sync([$tenant->id]);

    return $user;
}

function resolve_request(string $entity, string $method, array $content)
{
    $id = array_key_exists('id', $content) ? $content['id'] : 1;
    switch ($method) {
        case 'index':
            return ['method' => 'get', 'url' => $entity];
        case 'show':
            return ['method' => 'get', 'url' => "$entity/$id"];
        case 'create':
            return ['method' => 'get', 'url' => "$entity/create"];
        case 'store':
            return ['method' => 'post', 'url' => "$entity", 'content' => []];
        case 'edit':
            return ['method' => 'get', 'url' => "$entity/$id/edit"];
        case 'update':
            return ['method' => 'put', 'url' => "$entity/$id", 'content' => []];
        case 'destroy':
            return ['method' => 'delete', 'url' => "$entity/$id"];
    }
}

function generateModelsData()
{
    return [
        'tenants' => Tenant::factory()->make()->toArray(),
        'products' => Product::factory()->make()->toArray(),
        'brands' => Brand::factory()->make()->toArray(),
        'suppliers' => Supplier::factory()->make()->toArray(),
        'spas' => Spa::factory()->make()->toArray(),
        'users' => User::factory()->make()->toArray(),
        'tokens' => Token::factory()->make()->toArray(),
        'quotes' => Quote::factory()->make()->toArray(),
        'solution-types' => SolutionType::factory()->make()->toArray(),
        'apis' => Api::factory()->make()->toArray(),
        'api/accounts' => Account::factory()->make()->toArray(),
        'api/prices' => Price::factory()->make()->toArray(),
        'api/quote-items' => QuoteItem::factory()->make()->toArray(),
    ];
}

function runPermissionTest(array $permissions, array &$modelsData)
{
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
                $content = array_key_exists($entity, $modelsData) ? $modelsData[$entity] : [];
                $request = resolve_request($entity, $key, $content);
                $response =
                    array_key_exists('content', $request)
                        ? test()->{$request['method'].$append}($request['url'], $content)
                        : test()->{$request['method'].$append}($request['url']);
                if ($hasPermission) {
                    expect(in_array($response->status(), [200, 201, 204, 302]))->toBeTrue();
                } else {
                    $response->isForbidden();
                }

                if ($key == 'store' && $permissions[$entity]['index']) {
                    $url = str_replace('api/', '', $entity);
                    $res = test()->get("api/$url", ['order' => 'desc']);
                    $modelsData[$entity] = $res->json()['data'][0];
                }

            } catch (Exception $exception) {
                $message = $exception->getMessage();
                $error = json_encode($response->json());

                throw new Exception("Permissions validation errors on Company Admin: [Entity: $entity, Method: $key]\n\nContext:\n$message\nMessage: $error");
            }
        }
    }
}

function createQuoteWithItems($testInstance)
{
    $response = $testInstance->post('api/quotes', $testInstance->quote_data);
    $response->assertSuccessful();
    $quote = $response->json();

    $response = $testInstance->post('api/quote-items', [...$testInstance->quote_item, 'quote_id' => $quote['id']]);
    $response->assertSuccessful();

    return $testInstance->get('api/quotes/'.$quote['id']);
}

function setTenantForPermissions($id = 1)
{
    // Set the current team context
    app(PermissionRegistrar::class)->setPermissionsTeamId(null);
    $tenant = Tenant::find($id);
    app(PermissionRegistrar::class)->setPermissionsTeamId($tenant->id);
}
