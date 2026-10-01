<?php

use App\Models\Category;
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

    $this->parent = Category::factory()->create(['code' => 'cat-parent', 'name' => 'Parent']);
    $this->child = Category::factory()->create(['code' => 'cat-child', 'name' => 'Child', 'parent_id' => $this->parent->id, 'is_active' => 1]);
});

function fakeCategoriesFile(string $content): UploadedFile
{
    return UploadedFile::fake()->createWithContent('categories_import.csv', $content);
}

it('skips a category whose code already exists when not overwriting', function () {
    $csv = '"Code","Name","Is Active","Parent Code"
"cat-child","Renamed","0",""';

    $response = $this->post('/categories/import', [
        'file' => fakeCategoriesFile($csv),
        'dry_run' => 1,
        'overwrite' => 0,
    ]);

    expect($response->json('results.skipped'))->toBe(1);
});

it('updates the category with the same code when overwriting, clearing a blank parent', function () {
    $csv = '"Code","Name","Is Active","Parent Code"
"cat-child","Renamed","0",""';

    $this->post('/categories/import', [
        'file' => fakeCategoriesFile($csv),
        'dry_run' => 0,
        'overwrite' => 1,
    ])->assertSuccessful();

    $child = $this->child->fresh();

    expect(Category::where('code', 'cat-child')->count())->toBe(1)
        ->and($child->name)->toBe('Renamed')
        ->and((int) $child->is_active)->toBe(0)
        ->and($child->parent_id)->toBeNull();
});

it('rejects re-parenting a category under one of its subcategories', function () {
    $csv = '"Code","Name","Is Active","Parent Code"
"cat-parent","Parent","1","cat-child"';

    $response = $this->post('/categories/import', [
        'file' => fakeCategoriesFile($csv),
        'dry_run' => 1,
        'overwrite' => 1,
    ]);

    expect($response->json('results.skipped'))->toBe(1)
        ->and($response->json('results.success'))->toBe(0);
});
