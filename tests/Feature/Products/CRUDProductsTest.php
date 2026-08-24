<?php

use App\Models\Product;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

/**
 * Helper: base valid payload to create a product.
 */
function validRenewablePayload(array $overrides = []): array
{
    return array_merge([
        'sku' => 'SKU-'.fake()->unique()->numerify('####'),
        'name' => 'Test Product',
        'price_plan_id' => 1,
        'contract_term_value' => 12,
        'contract_term_unit' => 'MONTHS',
        'billing_frequency_value' => 1,
        'billing_frequency_unit' => 'MONTHS',
        'prices' => [
            ['cost' => 10, 'net' => 15],
        ],
    ], $overrides);
}

beforeEach(function () {
    $this->artisan('db:seed');
    setTenantForPermissions();
    $this->user = User::factory()->create();
    $this->user->tenants()->attach(Tenant::find(1));
    $this->user->assignRole('Company Admin');

    Auth::login($this->user);
    $this->actingAs($this->user);
});

it('creates a product with valid renewable payload and divisible billing frequency', function () {
    $payload = validRenewablePayload();
    $response = $this->postJson('/products', $payload);

    $response->assertRedirect();
    $this->assertDatabaseHas('products', ['sku' => $payload['sku'] ?? null]);
});

it('fails to create when billing frequency does not evenly divide Renewal term', function () {
    $response = $this->postJson('/products', validRenewablePayload([
        'contract_term_value' => 12,
        'contract_term_unit' => 'MONTHS',
        'billing_frequency_value' => 5,
        'billing_frequency_unit' => 'MONTHS',
    ]));

    // 12 months = 360 days; 5 months = 150 days; 360 % 150 !== 0
    $response->assertInvalid(['billing_frequency_value']);
});

it('updates a product with valid divisible billing frequency', function () {
    $product = Product::factory()->create();

    $response = $this->putJson("/products/{$product->id}", [
        'sku' => $product->sku,
        'name' => $product->name,
        'price_plan_id' => 1,
        'contract_term_value' => 12,
        'contract_term_unit' => 'MONTHS',
        'billing_frequency_value' => 3,
        'billing_frequency_unit' => 'MONTHS',
    ]);

    $response->assertRedirect();
});

it('fails when billing frequency does not evenly divide Renewal term', function () {
    $product = Product::factory()->create();

    $response = $this->putJson("/products/{$product->id}", [
        'sku' => $product->sku,
        'price_plan_id' => 1,
        'contract_term_value' => 12,
        'contract_term_unit' => 'MONTHS',
        'billing_frequency_value' => 5,
        'billing_frequency_unit' => 'MONTHS',
    ]);

    $response->assertInvalid(['billing_frequency_value']);
});
