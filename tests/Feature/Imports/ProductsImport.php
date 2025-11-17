<?php

use App\Models\Category;
use App\Models\Product;
use App\Models\Step;
use App\Models\Supplier;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->artisan('db:seed');
    $this->user = User::factory()->create();
    $this->user->tenants()->attach(Tenant::find(1));
    $this->user->assignRole('Company Admin');
    Category::factory(1)->create();
    Step::factory(1)->create();
    Supplier::factory(1)->create();

    Auth::login($this->user);
    $this->actingAs($this->user);

    $this->fileContent = '"sku","name","description","supplier_id","categories","steps","status","expires_at","price_code","price_cost","price_net","price_plan_id","parent_sku"
"good","Good","Good","1","1","1","Active","31/12/2100","1001","50.00","75.00","1",""
"good_with_sku","Good with SKU","Good with SKU","","1","1","","","1003","10.00","15.00","1","good"
"","Missing SKU","Missing SKU","1","1","1","","31/12/2025","1004","10.00","15.00","1",""
"missing_name","","Missing Name","1","1","1","","31/12/2025","1005","10.00","15.00","1",""
"missing_price_cost","Missing Price Cost","Missing Price Cost","1","1","1","","31/12/2025","","","15.00","1",""
"missing_price_net","Missing Price Net","Missing Price Net","1","1","1","","31/12/2025","1008","10.00","","1",""
"missing_price_plan_id","Missing Price Plan ID","Missing Price Plan ID","1","1","1","","31/12/2025","1009","10.00","15.00","",""
"bad_supplier_id","Bad Supplier ID","Bad Supplier ID","10","1","","31/12/2025","1010","10.00","15.00","1",""
"bad_category_id","Bad Category ID","Bad Category ID","1","99999","","31/12/2025","1011","10.00","15.00","1",""
"bad_parent_sku","Bad Parent SKU","Bad Parent SKU","1","1","1","","31/12/2025","1012","10.00","15.00","1","99999"
"wrong_date_format","Wrong date format","Wrong date format","1","","","12/31/2025","1013","10.00","15.00","1",""
"bad_price_plan_id","Bad Price Plan ID","Bad Price Plan ID","1","1","1","","31/12/2025","1014","10.00","15.00","99999",""';

    $this->updateFileContent = '"sku","name","description","supplier_id","categories","steps","status","expires_at","price_code","price_cost","price_net","price_plan_id","parent_sku"
"good","Good Updated","Good Updated","1","1","1","Active","31/12/2100","1001","50.00","75.00","1",""
"good_with_sku","Good with SKU Updated","Good with SKU Updated","","1","","","","1003","10.00","15.00","1","good"';

    $this->file = UploadedFile::fake()
        ->createWithContent('products_import.csv', $this->fileContent);

    $this->updateFile = UploadedFile::fake()
        ->createWithContent('products_import.csv', $this->updateFileContent);
});

it('The user can upload a file and get an import preview', function () {
    $response = $this->post('/api/imports', [
        'file' => $this->file,
        'overwrite' => 0,
        'model' => 'Product',
    ]);
    $response->assertSuccessful();
    $previewData = $response->json();

    expect(Product::query()->count())->toBe(0)
        ->and($previewData['model'])->toBe('Product')
        ->and($previewData)->toHaveKey('results')
        ->and((int) $previewData['overwrite'])->toBe(0)
        ->and($previewData['results']['processed'])->toBe(12)
        ->and($previewData['results']['skipped'])->toBe(10)
        ->and($previewData['results']['success'])->toBe(2);
});

it('The User can Import file content after preview', function () {
    $response = $this->post('/api/imports', [
        'file' => $this->file,
        'overwrite' => 0,
        'model' => 'Product',
    ]);
    $response->assertSuccessful();
    $previewData = $response->json();

    expect(Product::query()->count())->toBe(0)
        ->and($previewData['model'])->toBe('Product')
        ->and($previewData)->toHaveKey('results')
        ->and((int) $previewData['overwrite'])->toBe(0)
        ->and($previewData['results']['processed'])->toBe(12)
        ->and($previewData['results']['skipped'])->toBe(10)
        ->and($previewData['results']['success'])->toBe(2);

    $importResponse = $this->post('/imports', [
        'file' => $this->file,
        'overwrite' => 0,
        'model' => 'Product',
    ]);

    $importResponse->assertRedirect();

    expect(Product::query()->count())->toBe(2);
});

it('The User can not update products if the overwrite flag is not checked', function () {
    $response = $this->post('/api/imports', [
        'file' => $this->file,
        'overwrite' => 0,
        'model' => 'Product',
    ]);
    $response->assertSuccessful();
    $previewData = $response->json();

    expect(Product::query()->count())->toBe(0)
        ->and($previewData['model'])->toBe('Product')
        ->and($previewData)->toHaveKey('results')
        ->and((int) $previewData['overwrite'])->toBe(0)
        ->and($previewData['results']['processed'])->toBe(12)
        ->and($previewData['results']['skipped'])->toBe(10)
        ->and($previewData['results']['success'])->toBe(2);

    $importResponse = $this->post('/imports', [
        'file' => $this->file,
        'overwrite' => 0,
        'model' => 'Product',
    ]);

    $importResponse->assertRedirect();

    expect(Product::query()->count())->toBe(2);

    $response = $this->post('/api/imports', [
        'file' => $this->updateFile,
        'overwrite' => 0,
        'model' => 'Product',
    ]);

    $response->assertSuccessful();
    $previewData = $response->json();

    expect(Product::query()->count())->toBe(2)
        ->and($previewData['model'])->toBe('Product')
        ->and($previewData)->toHaveKey('results')
        ->and((int) $previewData['overwrite'])->toBe(0)
        ->and($previewData['results']['processed'])->toBe(2)
        ->and($previewData['results']['skipped'])->toBe(2)
        ->and($previewData['results']['success'])->toBe(0);

    $importResponse = $this->post('/imports', [
        'file' => $this->updateFile,
        'overwrite' => 0,
        'model' => 'Product',
    ]);

    $importResponse->assertRedirect();

    $products = Product::all()->pluck('name')->toArray();

    expect(count($products))->toBe(2)
        ->and($products)->toBe(['Good', 'Good with SKU']);
});

it('The User can update products if the overwrite flag is checked', function () {
    $response = $this->post('/api/imports', [
        'file' => $this->file,
        'overwrite' => 0,
        'model' => 'Product',
    ]);
    $response->assertSuccessful();
    $previewData = $response->json();

    expect(Product::query()->count())->toBe(0)
        ->and($previewData['model'])->toBe('Product')
        ->and($previewData)->toHaveKey('results')
        ->and((int) $previewData['overwrite'])->toBe(0)
        ->and($previewData['results']['processed'])->toBe(12)
        ->and($previewData['results']['skipped'])->toBe(10)
        ->and($previewData['results']['success'])->toBe(2);

    $importResponse = $this->post('/imports', [
        'file' => $this->file,
        'overwrite' => 0,
        'model' => 'Product',
    ]);

    $importResponse->assertRedirect();

    expect(Product::query()->count())->toBe(2);

    $response = $this->post('/api/imports', [
        'file' => $this->updateFile,
        'overwrite' => 1,
        'model' => 'Product',
    ]);

    $response->assertSuccessful();
    $previewData = $response->json();

    expect(Product::query()->count())->toBe(2)
        ->and($previewData['model'])->toBe('Product')
        ->and($previewData)->toHaveKey('results')
        ->and((int) $previewData['overwrite'])->toBe(1)
        ->and($previewData['results']['processed'])->toBe(2)
        ->and($previewData['results']['skipped'])->toBe(0)
        ->and($previewData['results']['success'])->toBe(2);

    $importResponse = $this->post('/imports', [
        'file' => $this->updateFile,
        'overwrite' => 1,
        'model' => 'Product',
    ]);

    $importResponse->assertRedirect();

    $products = Product::all()->pluck('name')->toArray();

    expect(count($products))->toBe(2)
        ->and($products)->toBe(['Good Updated', 'Good with SKU Updated']);
});
