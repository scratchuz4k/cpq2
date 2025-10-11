<?php

use App\Models\Account;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->artisan('db:seed');
    $this->user = User::factory()->create();
    $this->user->tenants()->attach(Tenant::find(1));
    $this->user->assignRole('Sales Rep');
    $this->account = Account::factory()->create();
});

it('Create Quote and download PDF', function () {
    $quote_data = [
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

    $response = $this->actingAs($this->user)->post('api/quotes', $quote_data);
    $quote = $response->json();

    $pdfResponse = $this->actingAs($this->user)->get('quotes/'.$quote['hash'].'/download');
    $pdfResponse->assertSuccessful();
    $pdfResponse->assertHeader('Content-Type', 'application/pdf');
    $this->assertTrue(
        str_contains($pdfResponse->headers->get('Content-Disposition'), 'attachment'),
        'Expected "Content-Disposition" header to contain "attachment".'
    );
    expect($pdfResponse->getContent())->not->toBeEmpty();
});

it('Download shared Quote PDF without login ', function () {
    $quote_data = [
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

    $response = $this->actingAs($this->user)->post('api/quotes', $quote_data);
    $quote = $response->json();

    $sharedLink = $this->actingAs($this->user)->post('api/shared-links', [
        'shareable_id' => 2,
        'shareable_type' => 'quotes',
    ]);
    $sharedLink->assertSuccessful();

    $this->withoutToken();

    $pdfResponse = $this->withoutMiddleware([
        \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
    ])->post('quotes/'.$quote['hash'].'/download');
    $pdfResponse->assertSuccessful();
    $pdfResponse->assertHeader('Content-Type', 'application/pdf');
    $this->assertTrue(
        str_contains($pdfResponse->headers->get('Content-Disposition'), 'attachment'),
        'Expected "Content-Disposition" header to contain "attachment".'
    );
    expect($pdfResponse->getContent())->not->toBeEmpty();
});

it('Download not shared Quote PDF without login', function () {
    $quote_data = [
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

    $response = $this->actingAs($this->user)->post('api/quotes', $quote_data);
    $quote = $response->json();

    $this->withoutToken();
    $pdfResponse = $this->withoutMiddleware([
        \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
    ])->post('quotes/'.$quote['hash'].'/download');
    $pdfResponse->assertNotFound();
    $pdfResponse->assertHeader('Content-Type', 'application/json');
    expect($pdfResponse->json()['message'])->toBe('Quote not available or not shared.');
});
