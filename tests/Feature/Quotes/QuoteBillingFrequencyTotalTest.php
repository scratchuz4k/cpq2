<?php

use App\Models\Account;
use App\Models\PricePlan;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->artisan('db:seed');
    setTenantForPermissions();

    $this->sales_rep = User::factory()->create();
    $this->sales_rep->tenants()->attach(Tenant::find(1));
    $this->sales_rep->assignRole('Sales Rep');

    $this->account = Account::factory()->create();

    Auth::login($this->sales_rep);
    $this->actingAs($this->sales_rep);

    $this->post('api/quotes', [
        'account_id' => 1,
        'solution_type_id' => 1,
    ]);

    $this->monthly_price_plan_id = PricePlan::where('code', 'MONTHLY')->value('id');
    $this->single_price_plan_id = PricePlan::where('code', 'SINGLE')->value('id');
});

it('computes the final total from renewal term and billing frequency per the CPQ-519 example', function () {
    // Microsoft 365 E5: £48.00, 15% discount, qty 200, 3 year renewal billed every 6 months -> 6 payments
    $this->post('api/quote-items', [
        'quote_id' => 1,
        'name' => 'Microsoft 365 E5',
        'quantity' => 200,
        'price' => 48.00,
        'discount' => 15,
        'price_plan_id' => $this->monthly_price_plan_id,
        'renewal_terms_value' => 36,
        'renewal_terms_unit' => 'MONTHS',
        'billing_frequency_value' => 6,
        'billing_frequency_unit' => 'MONTHS',
    ])->assertSuccessful();

    // Azure Backup: £1,500.00, 10% discount, qty 5, 720 day (24 month) renewal billed every 90 days -> 8 payments
    $this->post('api/quote-items', [
        'quote_id' => 1,
        'name' => 'Azure Backup',
        'quantity' => 5,
        'price' => 1500.00,
        'discount' => 10,
        'price_plan_id' => $this->monthly_price_plan_id,
        'renewal_terms_value' => 24,
        'renewal_terms_unit' => 'MONTHS',
        'billing_frequency_value' => 90,
        'billing_frequency_unit' => 'DAYS',
    ])->assertSuccessful();

    // Migration Services: one-off, £22,500.00
    $this->post('api/quote-items', [
        'quote_id' => 1,
        'name' => 'Migration Services',
        'quantity' => 1,
        'price' => 22500.00,
        'discount' => 0,
        'price_plan_id' => $this->single_price_plan_id,
    ])->assertSuccessful();

    $quote = $this->get('api/quotes/1')->json();
    $items = collect($quote['items'])->keyBy('name');

    expect($items['Microsoft 365 E5']['total'])->toEqual(48960.0);
    expect($items['Azure Backup']['total'])->toEqual(54000.0);
    expect($items['Migration Services']['total'])->toEqual(22500.0);
    expect($quote['total'])->toEqual(125460.0);
});
