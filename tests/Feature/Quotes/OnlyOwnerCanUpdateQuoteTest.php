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
    $this->tenant = Tenant::find(1);

    $this->company_admin = User::factory()->create([]);
    $this->company_admin->tenants()->attach($this->tenant->id);
    $this->company_admin->assignRole('Company Admin');

    $this->sales_user = User::factory()->create();
    $this->sales_user->tenants()->attach($this->tenant->id);
    $this->sales_user->assignRole('Sales Rep');

    $this->sales_user_child = User::factory()->create(['parent_id' => $this->sales_user->id]);
    $this->sales_user_child->tenants()->attach($this->tenant->id);
    $this->sales_user_child->assignRole('Sales Rep');

    $this->users = [
        $this->super_user,
        $this->company_admin,
        $this->sales_user,
        $this->sales_user_child
    ];

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
    $this->quote_item = [
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

    Auth::login($this->sales_user_child);
    $this->actingAs($this->sales_user_child);
});

it('Managers can add an item to quote', function () {
    $quotes = [];

    for ($i = 0; $i < 4; $i++) {
        $response = $this->post('api/quotes', $this->quote_data);
        $response->assertSuccessful();
        $quotes[$i] = $response->json();

        $response = $this->post('api/quote-items', [...$this->quote_item, 'quote_id' => $quotes[$i]['id']]);
        $response->assertSuccessful();
    }



    foreach ($this->users as $index => $user) {
        Auth::guard('web')->logout();
        Auth::guard('web')->login($user);
        $this->actingAs($user);

        $item_response = $this->post('api/quote-items', [...$this->quote_item, 'quote_id' => $quotes[$index]['id']]);

        $item_response->assertSuccessful();
    }
});

it('Managers can update the quote', function () {
    $quotes = [];
    $data = [
        'status' => QuoteStatusesEnum::REGISTRY->name
    ];

    for ($i = 0; $i < 4; $i++) {
        $response = $this->post('api/quotes', $this->quote_data);
        $response->assertSuccessful();
        $quote = $response->json();

        $item_response = $this->post('api/quote-items', [...$this->quote_item, 'quote_id' => $quote['id']]);
        $item_response->assertSuccessful();
        $quotes[$i] = $this->get('api/quotes/'.$quote['id'])->json();
    }


    foreach ($this->users as $index => $user) {
        Auth::guard('web')->logout();
        Auth::guard('web')->login($user); // Log in as the sales user
        $this->actingAs($user);

        $response = $this->put('api/quotes/' . $quotes[$index]['id'], $data);
        $response->assertSuccessful();
    }
});
