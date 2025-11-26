<?php

use App\Enums\QuoteStatusesEnum;
use App\Models\Account;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->artisan('db:seed');
    setTenantForPermissions();
    $this->sales_manager = User::factory()->create();
    $this->sales_manager->tenants()->attach(Tenant::find(1));
    $this->sales_manager->assignRole('Sales Manager');

    $this->sales_rep = User::factory()->create();
    $this->sales_rep->tenants()->attach(Tenant::find(1));
    $this->sales_rep->assignRole('Sales Rep');

    $this->company_admin = User::factory()->create();
    $this->company_admin->tenants()->attach(Tenant::find(1));
    $this->company_admin->assignRole('Company Admin');

    $this->account = Account::factory()->create();

    Auth::login($this->sales_rep);
    $this->actingAs($this->sales_rep);

    $quote_data = [
        'account_id' => 1,
        'solution_type_id' => 1,
    ];

    $this->post('api/quotes', $quote_data);

});

it('Complete Quote Without Flow', function () {
    $this->actingAs($this->sales_rep);
    $quote_item = [
        'quote_id' => 1,
        'product_id' => null,
        'quantity' => 1,
        'name' => 'ADSL 2+ MPF',
        'data' => [],
        'price' => 0,
        'price_monthly' => 13.59,
    ];

    $this->post('api/quote-items', $quote_item);

    $this->put('api/quotes/1', ['status' => QuoteStatusesEnum::COMPLETED->name]);

    $quote = $this->get('api/quotes/1')->json();
    $this->assertEquals('COMPLETED', $quote['status']);
});

it('Complete Quote With Margin Flow', function () {
    $this->actingAs($this->company_admin)
        ->put('api/tenants/1', [
            'configurations' => [
                'has_quote_approval' => '1',
                'quote_approval_flow' => 'margin',
                'quote_margin_rules_operation' => 'value',
                'quote_margin_rules_value' => '50',
            ],
        ]);
    $this->actingAs($this->company_admin)->post('/logout');

    $quoteApproval = $this->actingAs($this->sales_rep->refresh())
        ->post('api/quote-approvals', ['quote_id' => 1])->json();
    $this->assertEquals('Quote is not ready for approval.', $quoteApproval['message']);

    $this->actingAs($this->sales_rep);
    $quote_item = [
        'quote_id' => 1,
        'product_id' => null,
        'quantity' => 1,
        'name' => 'ADSL 2+ MPF',
        'data' => [],
        'price' => 0,
        'price_monthly' => 13.59,
    ];

    $quoteItem = $this->post('api/quote-items', $quote_item);
    $quoteItem->assertSuccessful();

    $quoteApproval = $this->actingAs($this->sales_rep)
        ->post('api/quote-approvals', ['quote_id' => 1])->json();
    $this->actingAs($this->sales_rep)
        ->get('api/quote-approvals', [
            'filters' => [
                ['column' => 'quote_id', 'operator' => '=', 'value' => 1],
            ],
        ])
        ->assertJsonCount(2, 'data');

    $quote = $this->get('api/quotes/1')->json();
    $this->assertEquals('PENDING_APPROVAL', $quote['status']);
    $this->actingAs($this->sales_rep)->post('/logout');

    $quoteApproval = $this->actingAs($this->sales_manager->refresh())
        ->put('api/quote-approvals/2', ['status' => 'approved'])->json();
    $this->assertEquals('approved', $quoteApproval['status']);
    $quote = $this->get('api/quotes/1')->json();
    $this->assertEquals('VALIDATED', $quote['status']);
    $this->actingAs($this->sales_manager)->post('/logout');

    $quote = $this->actingAs($this->sales_rep->refresh())
        ->post('api/quotes/1/complete')->json();
});
