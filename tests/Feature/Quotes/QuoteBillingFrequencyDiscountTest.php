<?php

use App\Models\Account;
use App\Models\PricePlan;
use App\Models\QuoteDiscount;
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

    $recurringItem = fn (string $name, float $price, int $quantity, float $discount, string $unit) => [
        'quote_id' => 1,
        'name' => $name,
        'quantity' => $quantity,
        'price' => $price,
        'discount' => $discount,
        'price_plan_id' => $this->monthly_price_plan_id,
        'renewal_terms_value' => 12,
        'renewal_terms_unit' => 'MONTHS',
        'billing_frequency_value' => 1,
        'billing_frequency_unit' => $unit,
    ];

    // 1 payment, total £100.00
    $this->post('api/quote-items', $recurringItem('Annual Licence', 100, 1, 0, 'YEARS'))->assertSuccessful();
    // £10.00 with a 50% product discount, 12 payments, total £120.00
    $this->post('api/quote-items', $recurringItem('Monthly Support', 10, 2, 50, 'MONTHS'))->assertSuccessful();
    // 360 payments, total £360.00
    $this->post('api/quote-items', $recurringItem('Daily Backup', 1, 1, 0, 'DAYS'))->assertSuccessful();

    $this->recurringDiscount = fn (string $unit, float $discount) => $this->post('api/quote-discounts', [
        'quote_id' => 1,
        'code' => 'MONTHLY',
        'entity' => 'price_plans',
        'entity_id' => $this->monthly_price_plan_id,
        'billing_frequency_unit' => $unit,
        'discount' => $discount,
    ]);
});

it('applies annual, monthly and daily discounts only to items with that billing frequency', function () {
    ($this->recurringDiscount)('YEARS', 10)->assertSuccessful();
    ($this->recurringDiscount)('MONTHS', 20)->assertSuccessful();
    ($this->recurringDiscount)('DAYS', 50)->assertSuccessful();

    $quote = $this->get('api/quotes/1')->json();
    $discounts = collect($quote['discounts'])->keyBy('billing_frequency_unit');

    expect($discounts['YEARS']['total'])->toEqual(10.0);
    expect($discounts['MONTHS']['value'])->toEqual(2.0);
    expect($discounts['MONTHS']['total'])->toEqual(24.0);
    expect($discounts['DAYS']['total'])->toEqual(180.0);
    expect($quote['total_discount'])->toEqual(214.0);
    expect($quote['total'])->toEqual(366.0);
});

it('applies the quote discount on top of the billing frequency discounts', function () {
    ($this->recurringDiscount)('YEARS', 10)->assertSuccessful();
    ($this->recurringDiscount)('MONTHS', 20)->assertSuccessful();
    ($this->recurringDiscount)('DAYS', 50)->assertSuccessful();

    $this->post('api/quote-discounts', [
        'quote_id' => 1,
        'code' => 'quote-1',
        'entity' => 'quote',
        'entity_id' => 1,
        'discount' => 10,
    ])->assertSuccessful();

    $quote = $this->get('api/quotes/1')->json();
    $quoteDiscount = collect($quote['discounts'])->firstWhere('discountable_type', 'App\Models\Quote');

    expect($quoteDiscount['total'])->toEqual(36.6);
    expect($quote['total'])->toEqual(329.4);
});

it('updates the existing discount for a billing frequency instead of duplicating it', function () {
    ($this->recurringDiscount)('MONTHS', 20)->assertSuccessful();
    ($this->recurringDiscount)('MONTHS', 50)->assertSuccessful();

    expect(QuoteDiscount::where('billing_frequency_unit', 'MONTHS')->count())->toBe(1);
    expect($this->get('api/quotes/1')->json('total'))->toEqual(520.0);
});

it('applies the recurring discount to all recurring items before the billing frequency discount', function () {
    $this->post('api/quote-discounts', [
        'quote_id' => 1,
        'code' => 'MONTHLY',
        'entity' => 'price_plans',
        'entity_id' => $this->monthly_price_plan_id,
        'discount' => 10,
    ])->assertSuccessful();
    ($this->recurringDiscount)('YEARS', 10)->assertSuccessful();

    $quote = $this->get('api/quotes/1')->json();
    $recurring = collect($quote['discounts'])->firstWhere('billing_frequency_unit', null);
    $annual = collect($quote['discounts'])->firstWhere('billing_frequency_unit', 'YEARS');

    expect($recurring['total'])->toEqual(58.0);
    expect($annual['total'])->toEqual(9.0);
    expect($quote['total'])->toEqual(513.0);
});

it('removes a billing frequency discount when no items with that billing frequency remain', function () {
    ($this->recurringDiscount)('DAYS', 50)->assertSuccessful();

    $dailyItemId = collect($this->get('api/quotes/1')->json('items'))->firstWhere('name', 'Daily Backup')['id'];
    $this->delete("api/quote-items/$dailyItemId")->assertSuccessful();

    expect(QuoteDiscount::where('billing_frequency_unit', 'DAYS')->count())->toBe(0);
    expect($this->get('api/quotes/1')->json('total'))->toEqual(220.0);
});
