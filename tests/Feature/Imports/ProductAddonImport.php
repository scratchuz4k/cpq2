<?php

use App\Models\Product;
use App\Models\ProductAddon;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
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

    $this->productA = Product::factory()->create(['sku' => 'ADDON-A']);
    $this->productB = Product::factory()->create(['sku' => 'ADDON-B']);
    $this->productC = Product::factory()->create(['sku' => 'ADDON-C']);
});

function fakeProductAddonFile(string $content): UploadedFile
{
    return UploadedFile::fake()->createWithContent('product_addons_import.csv', $content);
}

it('previews a product add-on import, reporting unknown SKUs as skipped', function () {
    $csv = '"Product SKU","Add-on Product SKU"
"ADDON-A","ADDON-B"
"ADDON-A","does-not-exist"';

    $response = $this->post('/product-addons/import', [
        'file' => fakeProductAddonFile($csv),
        'dry_run' => 1,
        'overwrite' => 0,
    ]);

    $response->assertSuccessful();

    expect(ProductAddon::query()->count())->toBe(0)
        ->and($response->json('results.processed'))->toBe(2)
        ->and($response->json('results.success'))->toBe(1)
        ->and($response->json('results.skipped'))->toBe(1);
});

it('creates a product add-on on confirm', function () {
    $csv = '"Product SKU","Add-on Product SKU"
"ADDON-A","ADDON-B"';

    $this->post('/product-addons/import', [
        'file' => fakeProductAddonFile($csv),
        'dry_run' => 0,
        'overwrite' => 0,
    ])->assertSuccessful();

    expect(ProductAddon::where('product_id', $this->productA->id)->where('addon_id', $this->productB->id)->exists())->toBeTrue();
});

it('rejects a self-referencing add-on', function () {
    $csv = '"Product SKU","Add-on Product SKU"
"ADDON-A","ADDON-A"';

    $response = $this->post('/product-addons/import', [
        'file' => fakeProductAddonFile($csv),
        'dry_run' => 1,
        'overwrite' => 0,
    ]);

    expect($response->json('results.skipped'))->toBe(1)
        ->and($response->json('results.success'))->toBe(0);
});

it('skips an add-on that already exists without erroring the rest of the file', function () {
    ProductAddon::create(['product_id' => $this->productA->id, 'addon_id' => $this->productB->id]);

    $csv = '"Product SKU","Add-on Product SKU"
"ADDON-A","ADDON-B"
"ADDON-A","ADDON-C"';

    $response = $this->post('/product-addons/import', [
        'file' => fakeProductAddonFile($csv),
        'dry_run' => 0,
        'overwrite' => 0,
    ]);

    $response->assertSuccessful();

    expect(ProductAddon::query()->count())->toBe(2)
        ->and(ProductAddon::where('product_id', $this->productA->id)->where('addon_id', $this->productC->id)->exists())->toBeTrue();
});

it('ignores the overwrite flag and still skips existing add-ons', function () {
    ProductAddon::create(['product_id' => $this->productA->id, 'addon_id' => $this->productB->id]);

    $csv = '"Product SKU","Add-on Product SKU"
"ADDON-A","ADDON-B"
"ADDON-A","ADDON-C"';

    $response = $this->post('/product-addons/import', [
        'file' => fakeProductAddonFile($csv),
        'dry_run' => 1,
        'overwrite' => 1,
    ]);

    expect($response->json('results.success'))->toBe(1)
        ->and($response->json('results.skipped'))->toBe(1);

    $this->post('/product-addons/import', [
        'file' => fakeProductAddonFile($csv),
        'dry_run' => 0,
        'overwrite' => 1,
    ])->assertSuccessful();

    expect(ProductAddon::where('product_id', $this->productA->id)->count())->toBe(2);
});
