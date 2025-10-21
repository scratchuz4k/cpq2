<?php

use App\Models\Account;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->artisan('db:seed');
    $this->account = Account::factory()->create();
    $this->tenant = Tenant::find(1);
    $this->tenant_2 = Tenant::factory()->create();
    $this->quote_data = [
        'account_id' => 1,
        'address_position' => [
            'lat' => 55.9580794,
            'lon' => -2.9826655,
        ],
        'address_city' => 'Prestonpans',
        'address_data' => [
            'addressKey' => 'A00005766451',
            'type' => 'Gold',
            'uprn' => '138015158',
            'districtCode' => 'ES',
            'coordinatesEasting' => '338745',
            'coordinatesNorthing' => '674332',
            'latitude' => 55.9580794,
            'longitude' => -2.9826655,
            'label' => '20, West Loan, Prestonpans, EH32 9NT, United Kingdom',
            'organisationName' => null,
            'subPremises' => null,
            'premisesName' => null,
            'dependentThoroughfareName' => null,
            'thoroughfareNumber' => '20',
            'thoroughfareName' => 'West Loan',
            'postTown' => 'Prestonpans',
            'postCode' => 'EH32 9NT',
            'country' => 'United Kingdom',
        ],
        'address_line1' => '20, West Loan, Prestonpans, EH32 9NT, United Kingdom',
        'address_postcode' => 'EH32 9NT',
        'solution_type_id' => 1,
    ];
});

it('Create Quote and download PDF', function () {
    $user = User::factory()->create();
    $user->tenants()->attach($this->tenant);
    $user->assignRole('Sales Rep');

    $response = $this->actingAs($user)->post('api/quotes', $this->quote_data);
    $quote = $response->json();

    $pdfResponse = $this->actingAs($user)->get('quotes/'.$quote['hash'].'/download');
    $pdfResponse->assertSuccessful();
    $pdfResponse->assertHeader('Content-Type', 'application/pdf');
    $this->assertTrue(
        str_contains($pdfResponse->headers->get('Content-Disposition'), 'attachment'),
        'Expected "Content-Disposition" header to contain "attachment".'
    );
    expect($pdfResponse->getContent())->not->toBeEmpty();
});

it('Download shared Quote PDF without login ', function () {
    $user = User::factory()->create();
    $user->tenants()->attach($this->tenant);
    $user->assignRole('Sales Rep');

    $response = $this->actingAs($user)->post('api/quotes', $this->quote_data);
    $quote = $response->json();

    $sharedLink = $this->actingAs($user)->post('api/shared-links', [
        'shareable_id' => $quote['id'],
        'shareable_type' => 'quotes',
    ]);
    $sharedLink->assertSuccessful();

    $response = $this->actingAs($user)->post('/logout');

    $pdfResponse = $this->actingAsGuest('web')->post('quotes/'.$quote['hash'].'/download');
    $pdfResponse->assertSuccessful();
    $pdfResponse->assertHeader('Content-Type', 'application/pdf');
    $this->assertTrue(
        str_contains($pdfResponse->headers->get('Content-Disposition'), 'attachment'),
        'Expected "Content-Disposition" header to contain "attachment".'
    );
    expect($pdfResponse->getContent())->not->toBeEmpty();
});

it('Download not shared Quote PDF without login', function () {
    $user = User::factory()->create();
    $user->tenants()->attach($this->tenant);
    $user->assignRole('Sales Rep');

    $response = $this->actingAs($user)->post('api/quotes', $this->quote_data);
    $quote = $response->json();

    $this->actingAs($user)->post('/logout');

    $pdfResponse = $this->actingAsGuest('web')->post('quotes/'.$quote['hash'].'/download');
    $pdfResponse->assertNotFound();
    $pdfResponse->assertHeader('Content-Type', 'application/json');
    expect($pdfResponse->json()['message'])->toBe('Quote not available or not shared.');
});

it('Download not shared Quote PDF from another tenant', function () {
    $user_t1 = User::factory()->create();
    $user_t1->tenants()->attach($this->tenant);
    $user_t1->assignRole('Sales Rep');

    $user_t2 = User::factory()->create();
    $user_t2->tenants()->attach($this->tenant_2);
    $user_t2->assignRole('Sales Rep');

    $response = $this->actingAs($user_t1)->post('api/quotes', $this->quote_data);
    $quote = $response->json();

    $this->actingAs($user_t1)->post('/logout');

    $pdfResponse = $this->actingAs($user_t2)->post('quotes/'.$quote['hash'].'/download');
    $pdfResponse->assertNotFound();
    $pdfResponse->assertHeader('Content-Type', 'application/json');
    expect($pdfResponse->json()['message'])->toBe('Quote not available or not shared.');
});
