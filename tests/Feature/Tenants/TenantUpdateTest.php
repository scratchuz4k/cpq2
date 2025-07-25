<?php

use App\Http\Resources\TenantResource;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->artisan('db:seed');
    $this->user = User::factory()->create();
    $this->user->tenants()->attach(Tenant::find(1));
    $this->user->assignRole('Super Admin');

    Auth::login($this->user);
    $this->actingAs($this->user);
    Storage::fake('local');
});

it('Upload a tenant logo', function () {
    $file = UploadedFile::fake()
        ->create('test-logo.png', 100, 'image/png');

    $resource = $this
        ->post('api/resources', [
            'file' => $file,
        ])
        ->json();

    $data = [
        'logo_id' => $resource['file']['id'],
    ];

    $response = $this->put('api/tenants/'.$this->user->currentTenant->id, $data);
    $tenant = $response->json();
    expect($tenant['logo_id'])
        ->toBe($resource['file']['id'])
        ->and($tenant['logo'])
        ->toBeArray()
        ->not->ToBeEmpty();
});

it('Remove tenant logo', function () {
    $data = [
        'logo_id' => null,
    ];

    $response = $this->put('api/tenants/'.$this->user->currentTenant->id, $data);
    $tenant = $response->json();
    expect($tenant['logo_id'])
        ->toBeNull()
        ->and($tenant['logo'])
        ->toBeNull();
});

it('Upload a tenant SSO File with valid XML', function () {
    $xml_content =
        '<?xml version="1.0" encoding="UTF-8"?>
        <root>
            <child>Example content</child>
        </root>';

    $file = UploadedFile::fake()
        ->createWithContent('test-xml.xml', $xml_content)
        ->mimeType('text/xml');

    $resource = $this
        ->post('api/resources', [
            'file' => $file,
        ])
        ->json();

    $data = [
        'sso_id' => $resource['file']['id'],
    ];

    $response = $this->put('api/tenants/'.$this->user->currentTenant->id, $data);
    $tenant = $response->json();

    $response->assertStatus(200);
    expect($tenant['sso_id'])
        ->toBe($resource['file']['id'])
        ->and($tenant['file'])
        ->toBeArray()
        ->not->ToBeEmpty();
});

it('Upload a tenant SSO File with invalid XML', function () {
    $xml_content = 'Invalid XML content';

    $file = UploadedFile::fake()
        ->createWithContent('test-xml.xml', $xml_content);

    $response = $this
        ->post('api/resources', [
            'file' => $file,
        ]);

    $response->assertStatus(422)
        ->assertJsonPath('message', 'The file must contain a valid XML.');
});

it('Update Order flow setting', function () {
    // Activate setting
    $tenant = (new TenantResource($this->user->currentTenant))->resolve();
    $tenant['configurations']['orders_flow_is_active'] = '1';

    $response = $this->put('api/tenants/'.$this->user->currentTenant->id, $tenant);
    $tenant = $response->json();

    $response->assertStatus(200);
    expect($tenant['configurations']['orders_flow_is_active'])->toBe('1');

    // Deactivate setting
    $tenant['configurations']['orders_flow_is_active'] = '0';
    $response = $this->put('api/tenants/'.$this->user->currentTenant->id, $tenant);
    $tenant = $response->json();
    $response->assertStatus(200);
    expect($tenant['configurations']['orders_flow_is_active'])->toBe('0');
});
