<?php

use App\Enums\QuoteStatusesEnum;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Tenant;
use App\Models\Account;
use Illuminate\Support\Facades\Auth;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->artisan('db:seed');
    $this->user = User::factory()->create();
    $this->user->tenants()->attach(Tenant::find(1));
    $this->user->assignRole('Sales Rep');
    $this->account = Account::factory()->create();

    Auth::login($this->user);
    $this->actingAs($this->user);
});

it('Assert Quote status equals configure on create', function () {
    $quote_data = [
        'account_id' => $this->account['id'],
        'address_position' => null,
        'address_data' => null,
        'solution_type_id' => 1,
    ];

    $response = $this->post('api/quotes', $quote_data);
    $quote = $response->json();

    $this->assertEquals($quote['status'], QuoteStatusesEnum::CONFIGURE->name);
});

it('Assert Quote status equals price on adding address', function () {
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

    $response = $this->post('api/quotes', $quote_data);
    $quote = $response->json();
    $this->assertEquals($quote['status'], QuoteStatusesEnum::PRICE->name);
});

it('Assert Quote status equals quote on adding items', function () { 
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

    $response = $this->post('api/quotes', $quote_data);
    $quote = $response->json();
    $this->assertEquals($quote['status'], QuoteStatusesEnum::PRICE->name);

    $quote_item = [
        'quote_id' => $quote['id'],
        'product_id' => null,
        'quantity' => 1,
        'name' => 'ADSL 2+ MPF',
        'data' => [
            'quoteId' => '902695610',
            'providerKey' => 'virtual1',
            'providerName' => 'Virtual1',
            'productName' => 'ADSL 2+ MPF',
            'productCode' => '902695972',
            'connectivitySupplierName' => 'Virtual1',
            'connectivityType' => 'Fibre',
            'bearer' => null,
            'speed' => 18,
            'priceMonthly' => 13.59,
            'priceAnnual' => 163.08,
            'priceInstall' => 0,
            'priceECC' => null,
            'contractTermMonths' => 60,
            'isContended' => null,
            'isManaged' => null,
            'isIndicative' => false,
            'isOffnet' => null,
            'additionalInfo' => [
                'Access Type' => 'ADSL',
            ],
            'isAsymmetricService' => false,
            'uploadSpeed' => null,
            'quoteResponseError' => null,
            'hasErrors' => false,
            'code' => '3c7cccaf4e1be3ee7c9b0a497f4c2729',
            'active_spas' => [],
        ],
        'price' => 0,
        'price_monthly' => 13.59,
    ];

    $response = $this->post('api/quote-items', $quote_item);
    $updated_quote = $response->json();
    $this->assertEquals($updated_quote['quote']['status'], QuoteStatusesEnum::QUOTE->name);
 });
