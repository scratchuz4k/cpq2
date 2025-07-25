<?php

use App\Enums\OrderStatusesEnum;
use App\Enums\QuoteStatusesEnum;
use App\Models\Account;
use App\Models\Order;
use App\Models\Quote;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

beforeEach(function () {
    $this->artisan('db:seed');
    $this->sales_rep = User::factory()->create();
    $this->sales_rep->tenants()->attach(Tenant::find(1));
    $this->sales_rep->assignRole('Sales Rep');
    $this->account = Account::factory()->create();

    $this->order_manager = User::factory()->create();
    $this->order_manager->tenants()->attach(Tenant::find(1));
    $this->order_manager->assignRole('Order Manager');

    Auth::login($this->sales_rep);
    $this->actingAs($this->sales_rep);

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

    $this->post('api/quotes', $quote_data);

    $quote_item = [
        'quote_id' => 1,
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

    $this->post('api/quote-items', $quote_item);
    $this->quote = $this->get('api/quotes')->json();

    $this->put('api/quotes/1', ['status' => QuoteStatusesEnum::REGISTRY->name]);
});

it('Assign Order', function () {
    $this->actingAs($this->order_manager);

    $order = Order::first();

    $response = $this->put('api/orders/'.$order->id, ['user_id' => $this->order_manager->id]);
    $updated_order = $response->json();

    $this->assertEquals($updated_order['user_id'], $this->order_manager->id);
});

it('Assert Order and Quote status equals rejected on Order reject', function () {
    $this->actingAs($this->order_manager);
    $order = Order::first();

    $data = [
        'note' => 'Rejection Note',
        'status' => OrderStatusesEnum::REJECTED->name,
        'user_id' => $this->order_manager->id,
    ];
    $response = $this->put('api/orders/'.$order->id, $data);
    $updated_order = $response->json();

    $this->assertEquals($updated_order['user_id'], $this->order_manager->id);
    $this->assertEquals($updated_order['status'], OrderStatusesEnum::REJECTED->name);
    $this->assertEquals($updated_order['note'], $data['note']);

    $quote = Quote::find($order->quote_id);
    $this->assertEquals($quote['status'], QuoteStatusesEnum::REJECTED->name);
});

it('Assert Order and Quote status equals ordered on Order Approval', function () {
    $this->actingAs($this->order_manager);
    $order = Order::first();

    $data = [
        'status' => OrderStatusesEnum::SUBMITTED->name,
        'user_id' => $this->order_manager->id,
    ];
    $response = $this->put('api/orders/'.$order->id, $data);
    $updated_order = $response->json();

    $this->assertEquals($updated_order['user_id'], $this->order_manager->id);
    $this->assertEquals($updated_order['status'], OrderStatusesEnum::SUBMITTED->name);

    $quote = Quote::find($order->quote_id);
    $this->assertEquals($quote['status'], QuoteStatusesEnum::SUBMITTED->name);
});
