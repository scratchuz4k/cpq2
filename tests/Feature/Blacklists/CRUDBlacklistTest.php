<?php

use App\Helpers\Files\BlacklistFile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Tenant;
use App\Models\Merchant;
use Illuminate\Support\Facades\Auth;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->artisan('db:seed');
    $this->user = User::factory()->create();
    $this->user->tenants()->attach(Tenant::find(1));
    $this->user->assignRole('Company Admin');

    Auth::login($this->user);
    $this->actingAs($this->user);
});

it('Blacklists CRUD', function () {
    $data = [
        'productName' => 'teste',
        'providerName' => null,
        'connectivitySupplierGroup' => null,
        'bearer' => [
            'operator' => null,
            'value' => null,
            'unit' => null
        ],
        'speed' => [
            'operator' => null,
            'value' => null,
            'unit' => null
        ],
        'contractTermMonths' => null
    ];

    $response = $this->post('blacklists', $data);
    $response->assertRedirect();

    $blacklist_file = new BlacklistFile($this->user->currentTenant);
    $file_content = $blacklist_file->getContent();
    expect(count($file_content['data']))->toBe(1);

    //Update
    $blacklistItem = $file_content['data'][1];


    $data['id'] = $blacklistItem['id'];
    $data['productName'] = 'teste2';

    $response = $this->put('blacklist/' . $blacklistItem['id'], $data);
    $response->assertRedirect();

    $blacklistItem = $blacklist_file->getContent()['data'][1];
    $this->assertEquals($blacklistItem['productName'], 'teste2');

    $response = $this->delete('api/blacklist/' . $blacklistItem['id']);
    $response->assertOk();
    expect($blacklist_file->exists())->toBe(false);
});
