<?php

use App\Models\Account;
use App\Models\Group;
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

    $this->account = Account::factory()->create(['name' => 'Old Name', 'reference' => 'ACC-1', 'is_active' => 1]);
});

function fakeAccountsFile(string $content): UploadedFile
{
    return UploadedFile::fake()->createWithContent('accounts_import.csv', $content);
}

it('skips an account whose reference already exists when not overwriting', function () {
    $csv = '"Name","Reference","Group","Is Active"
"New Name","ACC-1","","0"';

    $this->post('/accounts/import', [
        'file' => fakeAccountsFile($csv),
        'dry_run' => 0,
        'overwrite' => 0,
    ])->assertSuccessful();

    expect(Account::where('reference', 'ACC-1')->count())->toBe(2)
        ->and($this->account->fresh()->name)->toBe('Old Name');
});

it('updates the account with the same reference when overwriting', function () {
    $group = Group::factory()->create();
    $this->account->groups()->sync([$group->id => ['tenant_id' => $this->user->tenant_id]]);

    $csv = '"Name","Reference","Group","Is Active"
"New Name","ACC-1","","0"';

    $preview = $this->post('/accounts/import', [
        'file' => fakeAccountsFile($csv),
        'dry_run' => 1,
        'overwrite' => 1,
    ]);

    expect($preview->json('results.success'))->toBe(1)
        ->and($this->account->fresh()->name)->toBe('Old Name');

    $this->post('/accounts/import', [
        'file' => fakeAccountsFile($csv),
        'dry_run' => 0,
        'overwrite' => 1,
    ])->assertSuccessful();

    $account = $this->account->fresh();

    expect(Account::where('reference', 'ACC-1')->count())->toBe(1)
        ->and($account->name)->toBe('New Name')
        ->and((int) $account->is_active)->toBe(0)
        ->and($account->groups()->count())->toBe(0);
});
