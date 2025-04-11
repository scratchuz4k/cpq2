<?php

use App\Helpers\DynamicEmail;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->artisan('db:seed');
});
//TODO EMAILS: add the tests once we get the credentials
test('reset password link screen can be rendered', function () {
    $response = $this->get('/forgot-password');

    $response->assertStatus(200);
})->skip();

test('reset password link can be requested', function () {
    Mail::fake();

    $user = User::factory()->create();
    $user->tenants()->attach(Tenant::find(1));
    $user->assignRole('Company Admin');

    $this->post('/forgot-password', ['email' => $user->email]);

    // Assert the email was queued
    Mail::assertQueued(DynamicEmail::class);
})->skip();

test('reset password screen can be rendered', function () {
    Notification::fake();

    $user = User::factory()->create();
    $user->tenants()->attach(Tenant::find(1));
    $user->assignRole('Company Admin');

    $token = Password::createToken($user);

    $response = $this->get('/reset-password/'.$token);

    $response->assertStatus(200);
})->skip();

test('password can be reset with valid token', function () {
    Notification::fake();

    $user = User::factory()->create();
    $user->tenants()->attach(Tenant::find(1));
    $user->assignRole('Company Admin');

    $token = Password::createToken($user);

    $response = $this->post('/reset-password', [
        'token' => $token,
        'email' => $user->email,
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('login'));
})->skip();
