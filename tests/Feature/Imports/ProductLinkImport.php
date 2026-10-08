<?php

use App\Models\Product;
use App\Models\ProductLink;
use App\Models\Step;
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

    $this->productA = Product::factory()->create(['sku' => 'LINK-A']);
    $this->productB = Product::factory()->create(['sku' => 'LINK-B']);
    $this->productC = Product::factory()->create(['sku' => 'LINK-C']);
});

function fakeProductLinkFile(string $content): UploadedFile
{
    return UploadedFile::fake()->createWithContent('product_links_import.csv', $content);
}

it('previews a product link import, reporting unknown SKUs as skipped', function () {
    $csv = '"Product SKU","Linked Product SKU"
"LINK-A","LINK-B"
"LINK-A","does-not-exist"';

    $response = $this->post('/product-links/import', [
        'file' => fakeProductLinkFile($csv),
        'dry_run' => 1,
        'overwrite' => 0,
    ]);

    $response->assertSuccessful();

    expect(ProductLink::query()->count())->toBe(0)
        ->and($response->json('results.processed'))->toBe(2)
        ->and($response->json('results.success'))->toBe(1)
        ->and($response->json('results.skipped'))->toBe(1);
});

it('creates a product link on confirm', function () {
    $csv = '"Product SKU","Linked Product SKU"
"LINK-A","LINK-B"';

    $this->post('/product-links/import', [
        'file' => fakeProductLinkFile($csv),
        'dry_run' => 0,
        'overwrite' => 0,
    ])->assertSuccessful();

    expect(ProductLink::where('product_id', $this->productA->id)->where('child_id', $this->productB->id)->exists())->toBeTrue();
});

it('rejects a self-referencing link', function () {
    $csv = '"Product SKU","Linked Product SKU"
"LINK-A","LINK-A"';

    $response = $this->post('/product-links/import', [
        'file' => fakeProductLinkFile($csv),
        'dry_run' => 1,
        'overwrite' => 0,
    ]);

    expect($response->json('results.skipped'))->toBe(1)
        ->and($response->json('results.success'))->toBe(0);
});

it('skips a link that already exists without erroring the rest of the file', function () {
    ProductLink::create(['product_id' => $this->productA->id, 'child_id' => $this->productB->id]);

    $csv = '"Product SKU","Linked Product SKU"
"LINK-A","LINK-B"
"LINK-A","LINK-C"';

    $response = $this->post('/product-links/import', [
        'file' => fakeProductLinkFile($csv),
        'dry_run' => 0,
        'overwrite' => 0,
    ]);

    $response->assertSuccessful();

    expect(ProductLink::query()->count())->toBe(2)
        ->and(ProductLink::where('product_id', $this->productA->id)->where('child_id', $this->productC->id)->exists())->toBeTrue();
});

it('rejects a link between products that share a step', function () {
    $step = Step::factory()->create();
    $this->productA->steps()->sync([$step->id]);
    $this->productB->steps()->sync([$step->id]);

    $csv = '"Product SKU","Linked Product SKU"
"LINK-A","LINK-B"';

    $response = $this->post('/product-links/import', [
        'file' => fakeProductLinkFile($csv),
        'dry_run' => 1,
        'overwrite' => 0,
    ]);

    expect($response->json('results.skipped'))->toBe(1)
        ->and($response->json('results.success'))->toBe(0);
});

it('ignores the overwrite flag and still skips existing links', function () {
    ProductLink::create(['product_id' => $this->productA->id, 'child_id' => $this->productB->id]);

    $csv = '"Product SKU","Linked Product SKU"
"LINK-A","LINK-B"';

    $preview = $this->post('/product-links/import', [
        'file' => fakeProductLinkFile($csv),
        'dry_run' => 1,
        'overwrite' => 1,
    ]);

    expect($preview->json('results.skipped'))->toBe(1)
        ->and($preview->json('results.success'))->toBe(0);

    $this->post('/product-links/import', [
        'file' => fakeProductLinkFile($csv),
        'dry_run' => 0,
        'overwrite' => 1,
    ])->assertSuccessful();

    expect(ProductLink::where('product_id', $this->productA->id)->count())->toBe(1);
});
