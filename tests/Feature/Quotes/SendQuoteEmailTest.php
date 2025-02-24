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
    $this->super_user = User::find(1);

    $this->sales_user = User::factory()->create();
    $this->sales_user->tenants()->attach(Tenant::find(1));
    $this->sales_user->assignRole('Sales Rep');

    $this->account = Account::factory()->create();

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

    Auth::login($this->super_user);
    $this->actingAs($this->super_user);
});

it('Only owner can add an item to quote', function () {
    $response = $this->post('api/quotes', $this->quote_data);
    $response->assertSuccessful();
    $quote = $response->json();
    $id = $quote['id'];
    $response = $this->post("quotes/$id/email", []);

    $response->assertSuccessful();
});
