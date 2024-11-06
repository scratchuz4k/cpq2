<?php

use App\Models\Spa;
use App\Models\User;
use App\Models\Tenant;
use App\Helpers\Prices\PriceRules;
use Illuminate\Support\Facades\Auth;
use App\Helpers\Prices\Rules\RuleUser;
use App\Helpers\Prices\Operations\OperationAdd;

test('User without Spa', function () {
    $user = User::factory()->create();
    // Set the user as the authenticated user
    Auth::login($user);

    // Alternatively, you can use actingAs
    $this->actingAs($user);

    $prices = new PriceRules();
    $value = $prices->calculate(1);

    expect($value)->toBe(1.0);
});


test('User with OperationAdd Spa', function () {
    $user = User::factory()->create();
    $tenant = Tenant::factory()->create();
    // Set the user as the authenticated user
    Auth::login($user);

    // Alternatively, you can use actingAs
    $this->actingAs($user);

    $spa = new Spa();
    $spa->tenant_id = $tenant->id;
    $spa->rule_type = RuleUser::class;
    $spa->field_1 = $user->id;
    $spa->operation = OperationAdd::class;
    $spa->value = 10;
    $spa->save();


    $prices = new PriceRules();
    $value = $prices->calculate(1);
    expect($value)->toBe(11.0);
});


test('User with OperationSubtract Spa', function () {
    $user = User::factory()->create();
    $tenant = Tenant::factory()->create();
    // Set the user as the authenticated user
    Auth::login($user);

    // Alternatively, you can use actingAs
    $this->actingAs($user);

    $spa = new Spa();
    $spa->tenant_id = $tenant->id;
    $spa->rule_type = RuleUser::class;
    $spa->field_1 = $user->id;
    $spa->operation = OperationAdd::class;
    $spa->value = 10;
    $spa->save();


    $prices = new PriceRules();
    $value = $prices->calculate(1);
    expect($value)->toBe(11.0);
});
