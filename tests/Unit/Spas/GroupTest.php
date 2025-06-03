<?php

use App\Enums\SpaOperationsEnum;
use App\Enums\SpaRulesEnum;
use App\Helpers\Prices\PriceRules;
use App\Models\Group;
use App\Models\Spa;
use App\Models\SpaOperation;
use App\Models\SpaRule;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

beforeEach(function () {
    // Declaration of values
    $this->artisan('db:seed');
    $this->group = Group::factory()->create();
    $this->user = User::factory()->create(['group_id' => $this->group->id]);

    // Set the user as the authenticated user
    Auth::login($this->user);
    $this->actingAs($this->user);
});

test('Group without Spa', function () {
    $prices = new PriceRules;
    $value = $prices->calculate(1);

    expect($value)->toBe(1.0);
    expect(count($prices->getAppliedSpas()))->toBe(0);
});

test('Product with OperationMargin Spa', function () {
    $spa = new Spa;
    $spa->tenant_id = 1;
    $spa->rule_id = SpaRule::where('code', SpaRulesEnum::Group->name)->value('id');
    $spa->field_1 = $this->group->id;
    $spa->operation_id = SpaOperation::where('code', SpaOperationsEnum::Margin->name)->value('id');
    $spa->value = 10;
    $spa->save();

    $prices = new PriceRules;
    $value = $prices->calculate(10);
    expect($value)->toBe(20.0);
    expect(count($prices->getAppliedSpas()))->toBe(1);

    $spa->value = 0;
    $spa->percentage = 10;
    $spa->save();
    $value = $prices->calculate(10);
    expect($value)->toBe(11.0);
    expect(count($prices->getAppliedSpas()))->toBe(1);
});

test('Product with OperationDiscount Spa', function () {
    $spa = new Spa;
    $spa->tenant_id = 1;
    $spa->rule_id = SpaRule::where('code', SpaRulesEnum::Group->name)->value('id');
    $spa->field_1 = $this->group->id;
    $spa->operation_id = SpaOperation::where('code', SpaOperationsEnum::Discount->name)->value('id');
    $spa->value = 10;
    $spa->save();

    $prices = new PriceRules;
    $value = $prices->calculate(10);
    expect($value)->toBe(0.0);
    expect(count($prices->getAppliedSpas()))->toBe(1);

    $spa->value = 0;
    $spa->percentage = 10;
    $spa->save();
    $value = $prices->calculate(10);
    expect($value)->toBe(9.0);
    expect(count($prices->getAppliedSpas()))->toBe(1);
});
