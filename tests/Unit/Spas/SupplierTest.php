<?php

use App\Models\Spa;
use App\Models\User;
use App\Models\Tenant;
use App\Models\Product;
use App\Models\SpaRule;
use App\Models\Supplier;
use App\Models\SpaOperation;
use App\Helpers\Prices\PriceRules;
use Illuminate\Support\Facades\Auth;
use App\Helpers\Prices\Enums\SpaRulesEnum;
use App\Helpers\Prices\Enums\SpaOperationsEnum;

beforeEach(function () {
    // Declaration of values
    $this->artisan('db:seed');
    $this->user = User::factory()->create();
    $this->tenant = Tenant::factory()->create();
    $this->supplier = Supplier::factory()->create();

    // Set the user as the authenticated user
    Auth::login($this->user);
    $this->actingAs($this->user);
});

test('Supplier without Spa', function () {
    $prices = new PriceRules();
    $value = $prices->calculate(1, supplier: $this->supplier);

    expect($value)->toBe(1.0);
    expect(count($prices->getAppliedSpas()))->toBe(0);
});


test('Supplier with OperationMargin Spa', function () {
    $spa = new Spa();
    $spa->tenant_id = $this->tenant->id;
    $spa->rule_id = SpaRule::where('code', SpaRulesEnum::Supplier->name)->value('id');
    $spa->field_1 = $this->supplier->id;
    $spa->operation_id = SpaOperation::where('code',  SpaOperationsEnum::Margin->name)->value('id');
    $spa->value = 10;
    $spa->save();

    $prices = new PriceRules();
    $value = $prices->calculate(10, supplier: $this->supplier);
    expect($value)->toBe(20.0);
    expect(count($prices->getAppliedSpas()))->toBe(1);

    $spa->value = 0;
    $spa->percentage = 10;
    $spa->save();
    $value = $prices->calculate(10, supplier: $this->supplier);
    expect($value)->toBe(11.0);
    expect(count($prices->getAppliedSpas()))->toBe(1);
});


test('Supplier with OperationDiscount Spa', function () {
    $spa = new Spa();
    $spa->tenant_id = $this->tenant->id;
    $spa->rule_id = SpaRule::where('code', SpaRulesEnum::Supplier->name)->value('id');
    $spa->field_1 = $this->supplier->id;
    $spa->operation_id = SpaOperation::where('code',  SpaOperationsEnum::Discount->name)->value('id');
    $spa->value = 10;
    $spa->save();

    $prices = new PriceRules();
    $value = $prices->calculate(10, supplier: $this->supplier);
    expect($value)->toBe(0.0);
    expect(count($prices->getAppliedSpas()))->toBe(1);

    $spa->value = 0;
    $spa->percentage = 10;
    $spa->save();
    $value = $prices->calculate(10, supplier: $this->supplier);
    expect($value)->toBe(9.0);
    expect(count($prices->getAppliedSpas()))->toBe(1);
});
