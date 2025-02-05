<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Tenant;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use App\Models\Resource;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->artisan('db:seed');
    $this->user = User::factory()->create();
    $this->user->tenants()->attach(Tenant::find(1));
    $this->user->assignRole('Super Admin');

    Auth::login($this->user);
    $this->actingAs($this->user);
});

it('upload file', function () {
    Storage::fake('local');

    $file = UploadedFile::fake()->create('test-logo.png', 100, 'image/png');

    $response = $this->post('api/resources', [
        'file' => $file,
    ]);

    $resource = $response->json();
    $response->assertStatus(200);
    expect($resource['message'])
        ->toBe('Resource uploaded successfully!')
        ->and($resource['file'])
        ->toBeArray()
        ->not->toBeEmpty();
});

it('remove file', function () {
    Storage::fake('local');

    $file = UploadedFile::fake()->create('test-logo.png', 100, 'image/png');

    $response = $this->post('api/resources', [
        'file' => $file,
    ]);

    $resource = $response->json();
    $response->assertStatus(200);
    expect($resource['message'])
        ->toBe('Resource uploaded successfully!')
        ->and($resource['file'])
        ->toBeArray()
        ->not->toBeEmpty();

    $firstResource = Resource::firstOrFail();
    $response = $this->delete('api/resources/' . $firstResource->id);
    $response->assertStatus(200);
    expect(Resource::count())->toBe(0);
});
