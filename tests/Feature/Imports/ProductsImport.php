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
    setTenantForPermissions();
    $this->user = User::factory()->create();
    $this->user->tenants()->attach(Tenant::find(1));
    $this->user->assignRole('Company Admin');
    Category::factory(1)->create();
    Step::factory(1)->create();
    Supplier::factory(1)->create();

    Auth::login($this->user);
    $this->actingAs($this->user);

    // price_plan_id 1 = Recurring (MONTHLY), 2 = One-Off (SINGLE) — see PricePlanEnum / InitTableSeeder.
    $this->fileContent = '"sku","name","description","categories","steps","status","expires_at","price_code","price_cost","price_net","price_plan_id","renewal_terms_value","renewal_terms_unit","billing_frequency_value","billing_frequency_unit"
"good_recurring","Good Recurring","Good Recurring","1","1","Active","31/12/2100","1001","50.00","75.00","1","12","Months","1","Months"
"good_oneoff","Good One-Off","Good One-Off","1","1","Active","31/12/2100","1002","10.00","15.00","2","","","",""
"","Missing SKU","Missing SKU","1","1","","31/12/2100","1004","10.00","15.00","2","","","",""
"missing_name","","Missing Name","1","1","","31/12/2100","1005","10.00","15.00","2","","","",""
"missing_price_cost","Missing Price Cost","Missing Price Cost","1","1","","31/12/2100","","","15.00","2","","","",""
"missing_price_net","Missing Price Net","Missing Price Net","1","1","","31/12/2100","1008","10.00","","2","","","",""
"missing_price_plan_id","Missing Price Plan ID","Missing Price Plan ID","1","1","","31/12/2100","1009","10.00","15.00","","","","",""
"bad_price_plan_id","Bad Price Plan ID","Bad Price Plan ID","1","1","","31/12/2100","1014","10.00","15.00","99999","","","",""
"bad_category_id","Bad Category ID","Bad Category ID","99999","1","","31/12/2100","1011","10.00","15.00","2","","","",""
"recurring_missing_terms","Recurring Missing Terms","Recurring Missing Terms","1","1","","31/12/2100","1015","10.00","15.00","1","","","",""
"recurring_bad_billing","Recurring Bad Billing","Recurring Bad Billing","1","1","","31/12/2100","1016","10.00","15.00","1","12","Months","5","Months"
"wrong_date_format","Wrong date format","Wrong date format","1","","","12/31/2025","1013","10.00","15.00","2","","","",""';

    $this->updateFileContent = '"sku","name","description","categories","steps","status","expires_at","price_code","price_cost","price_net","price_plan_id","renewal_terms_value","renewal_terms_unit","billing_frequency_value","billing_frequency_unit"
"good_recurring","Good Recurring Updated","Good Recurring Updated","1","1","Active","31/12/2100","1001","50.00","75.00","1","24","Months","12","Months"
"good_oneoff","Good One-Off Updated","Good One-Off Updated","1","1","","31/12/2100","1002","10.00","15.00","2","","","",""';

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
    $this->post('/api/imports', [
        'file' => $this->file,
        'overwrite' => 0,
        'model' => 'Product',
    ])->assertSuccessful();

    $importResponse = $this->post('/imports', [
        'file' => $this->file,
        'overwrite' => 0,
        'model' => 'Product',
    ]);

    $importResponse->assertRedirect();

    expect(Product::query()->count())->toBe(2);
});

it('populates renewal terms and billing frequency for a recurring product', function () {
    $this->post('/imports', [
        'file' => $this->file,
        'overwrite' => 0,
        'model' => 'Product',
    ])->assertRedirect();

    $product = Product::where('sku', 'good_recurring')->first();

    expect($product)->not->toBeNull()
        ->and($product->renewal_terms_value)->toBe(12)
        ->and($product->renewal_terms_unit)->toBe('MONTHS')
        ->and($product->billing_frequency_value)->toBe(1)
        ->and($product->billing_frequency_unit)->toBe('MONTHS');

    $oneOff = Product::where('sku', 'good_oneoff')->first();

    expect($oneOff->renewal_terms_value)->toBeNull()
        ->and($oneOff->renewal_terms_unit)->toBeNull()
        ->and($oneOff->billing_frequency_value)->toBeNull()
        ->and($oneOff->billing_frequency_unit)->toBeNull();
});

it('skips a recurring product missing renewal terms', function () {
    $response = $this->post('/api/imports', [
        'file' => $this->file,
        'overwrite' => 0,
        'model' => 'Product',
    ]);
    $response->assertSuccessful();

    $errors = collect($response->json('results.errors'));
    $rowErrors = $errors->firstWhere('row.sku', 'recurring_missing_terms');

    expect($rowErrors)->not->toBeNull()
        ->and(collect($rowErrors['errors'])->implode(' '))->toContain('renewal terms value');
});

it('skips a recurring product whose billing frequency does not divide the renewal term', function () {
    $response = $this->post('/api/imports', [
        'file' => $this->file,
        'overwrite' => 0,
        'model' => 'Product',
    ]);
    $response->assertSuccessful();

    $errors = collect($response->json('results.errors'));
    $rowErrors = $errors->firstWhere('row.sku', 'recurring_bad_billing');

    expect($rowErrors)->not->toBeNull()
        ->and(collect($rowErrors['errors'])->implode(' '))->toContain('billing frequency must evenly divide');
});

it('The User can not update products if the overwrite flag is not checked', function () {
    $this->post('/imports', [
        'file' => $this->file,
        'overwrite' => 0,
        'model' => 'Product',
    ])->assertRedirect();

    expect(Product::query()->count())->toBe(2);

    $response = $this->post('/api/imports', [
        'file' => $this->updateFile,
        'overwrite' => 0,
        'model' => 'Product',
    ]);

    $response->assertSuccessful();
    $previewData = $response->json();

    expect($previewData['results']['processed'])->toBe(2)
        ->and($previewData['results']['skipped'])->toBe(2)
        ->and($previewData['results']['success'])->toBe(0);

    $this->post('/imports', [
        'file' => $this->updateFile,
        'overwrite' => 0,
        'model' => 'Product',
    ])->assertRedirect();

    $products = Product::all()->pluck('name')->toArray();

    expect($products)->toBe(['Good Recurring', 'Good One-Off']);
});

it('The User can update products if the overwrite flag is checked', function () {
    $this->post('/imports', [
        'file' => $this->file,
        'overwrite' => 0,
        'model' => 'Product',
    ])->assertRedirect();

    expect(Product::query()->count())->toBe(2);

    $response = $this->post('/api/imports', [
        'file' => $this->updateFile,
        'overwrite' => 1,
        'model' => 'Product',
    ]);

    $response->assertSuccessful();
    $previewData = $response->json();

    expect($previewData['results']['processed'])->toBe(2)
        ->and($previewData['results']['skipped'])->toBe(0)
        ->and($previewData['results']['success'])->toBe(2);

    $this->post('/imports', [
        'file' => $this->updateFile,
        'overwrite' => 1,
        'model' => 'Product',
    ])->assertRedirect();

    $products = Product::all()->pluck('name')->toArray();

    expect($products)->toBe(['Good Recurring Updated', 'Good One-Off Updated']);

    $updated = Product::where('sku', 'good_recurring')->first();
    expect($updated->renewal_terms_value)->toBe(24)
        ->and($updated->billing_frequency_value)->toBe(12);
});
