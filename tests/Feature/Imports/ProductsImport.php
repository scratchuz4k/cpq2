<?php

use App\Models\Category;
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
    Category::factory(10)->create();
    Supplier::factory(10)->create();

    Auth::login($this->user);
    $this->actingAs($this->user);

    $this->fileConten = '"sku";"name";"description";"supplier_id";"category_id";"status";"expires_at";"price_code";"price_cost";"price_net";"price_plan_id";"parent_sku"
"CP1001";"Basic Talk Plan";"200 minutes of national calls per month with no data included.";"";"1";"Active";"05/11/2025";"927";"12";"620";"2";""
"CP1002";"Unlimited Talk & Text";"Unlimited national calls and SMS with 1GB of mobile data.";"";"1";"Active";"03/11/2025";"953";"255";"77";"1";"CP1003"
"CP1003";"Data Starter Plan";"3GB mobile data per month with pay-as-you-go calls.";"";"1";"Retired";"12/11/2025";"425";"794";"497";"1";""
"CP1004";"test parent_sku from DB";"3test parent_sku from DB";"";"1";"Retired";"12/11/2025";"425";"794";"497";"1";"basic"
"basic";"test missing parent_sku";"3test parent_sku from DB";"";"1";"Retired";"12/11/2025";"425";"794";"497";"1";"test"';
});

it('The user can uo', function () {
    $importContent =

    $file = UploadedFile::fake()
        ->createWithContent('products_import.csv', $this->fileConten);

    $response = $this->post('/api/imports', [
        'file' => $file,
        'overwrite' => 0,
        'model' => 'Product',
    ])->json();

    $previewData = $response->json();

});
