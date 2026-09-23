<?php

use App\Models\Product;
use App\Models\ProductBundle;
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

    $this->productA = Product::factory()->create(['sku' => 'BUNDLE-A']);
    $this->productB = Product::factory()->create(['sku' => 'BUNDLE-B']);
    $this->productC = Product::factory()->create(['sku' => 'BUNDLE-C']);
});

function fakeProductBundleFile(string $content): UploadedFile
{
    return UploadedFile::fake()->createWithContent('product_bundles_import.csv', $content);
}

it('previews a product bundle import, reporting unknown SKUs as skipped', function () {
    $csv = '"Product SKU","Bundle Item SKU","Bundle Item Quantity"
"BUNDLE-A","BUNDLE-B","2"
"BUNDLE-A","does-not-exist","1"';

    $response = $this->post('/product-bundles/import', [
        'file' => fakeProductBundleFile($csv),
        'dry_run' => 1,
        'overwrite' => 0,
    ]);

    $response->assertSuccessful();

    expect(ProductBundle::query()->count())->toBe(0)
        ->and($response->json('results.processed'))->toBe(2)
        ->and($response->json('results.success'))->toBe(1)
        ->and($response->json('results.skipped'))->toBe(1);
});

it('creates a bundle item with the given quantity on confirm', function () {
    $csv = '"Product SKU","Bundle Item SKU","Bundle Item Quantity"
"BUNDLE-A","BUNDLE-B","3"';

    $this->post('/product-bundles/import', [
        'file' => fakeProductBundleFile($csv),
        'dry_run' => 0,
        'overwrite' => 0,
    ])->assertSuccessful();

    $bundleItem = ProductBundle::where('product_id', $this->productA->id)->where('item_id', $this->productB->id)->first();

    expect($bundleItem)->not->toBeNull()
        ->and($bundleItem->quantity)->toBe(3);
});

it('rejects a zero or non-integer quantity', function () {
    $csv = '"Product SKU","Bundle Item SKU","Bundle Item Quantity"
"BUNDLE-A","BUNDLE-B","0"
"BUNDLE-A","BUNDLE-C","not-a-number"';

    $response = $this->post('/product-bundles/import', [
        'file' => fakeProductBundleFile($csv),
        'dry_run' => 1,
        'overwrite' => 0,
    ]);

    expect($response->json('results.skipped'))->toBe(2)
        ->and($response->json('results.success'))->toBe(0);
});

it('rejects a self-referencing bundle item', function () {
    $csv = '"Product SKU","Bundle Item SKU","Bundle Item Quantity"
"BUNDLE-A","BUNDLE-A","1"';

    $response = $this->post('/product-bundles/import', [
        'file' => fakeProductBundleFile($csv),
        'dry_run' => 1,
        'overwrite' => 0,
    ]);

    expect($response->json('results.skipped'))->toBe(1)
        ->and($response->json('results.success'))->toBe(0);
});

it('skips a bundle item that already exists and leaves its quantity untouched', function () {
    ProductBundle::create(['product_id' => $this->productA->id, 'item_id' => $this->productB->id, 'quantity' => 5]);

    $csv = '"Product SKU","Bundle Item SKU","Bundle Item Quantity"
"BUNDLE-A","BUNDLE-B","1"
"BUNDLE-A","BUNDLE-C","2"';

    $response = $this->post('/product-bundles/import', [
        'file' => fakeProductBundleFile($csv),
        'dry_run' => 0,
        'overwrite' => 0,
    ]);

    $response->assertSuccessful();

    expect(ProductBundle::query()->count())->toBe(2)
        ->and(ProductBundle::where('product_id', $this->productA->id)->where('item_id', $this->productB->id)->first()->quantity)->toBe(5)
        ->and(ProductBundle::where('product_id', $this->productA->id)->where('item_id', $this->productC->id)->first()->quantity)->toBe(2);
});
