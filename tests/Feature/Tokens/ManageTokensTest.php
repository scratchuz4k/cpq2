<?php

use App\Models\Permission;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->artisan('db:seed');
});

it('Create Token for first Tenant', function () {

    $this->user = User::factory()->create();
    $this->user->tenants()->attach(Tenant::find(1));

    setTenantForPermissions();
    $this->user->assignRole('Support Agent');

    Auth::login($this->user);
    $this->actingAs($this->user);

    $data = [
        'name' => 'test',
        'user_id' => $this->user->id,
        'is_active' => true,
    ];

    $response = $this->post('/tokens', $data);
    $response->assertRedirect();

    $response = $this->get('api/tokens');
    expect(count($this->get('api/tokens')->json()['data']))->toBe(1);
});

it('Create Token for second Tenant', function () {
    $super_user_email = env('SUPERUSER_EMAIL', 'super@example.com');
    $admin = User::where('email', $super_user_email)->first();
    $this->actingAs($admin);

    $data = [
        'name' => 'tenant 2',
        'domain' => 'tenant2.local',
        'database' => 'tenant2_db',
    ];

    $response = $this->post('/tenants', $data);
    $response->assertRedirect();

    $admin->refresh();

    $response = $this->get('/api/tenants');
    $response->assertOk();

    $response = $this->get('/tenant/2/make-current');
    $response->assertRedirect();

    $perms = Permission::where('name', 'like', '%token%')->get();
    $data = [
        'name' => 'Support Agent',
        'guard_name' => 'web',
        'permissions' => $perms->map(fn ($permission) => (int) $permission->id)->values()->toarray(),
    ];

    $response = $this->post('/roles', $data);
    $response->assertOk();

    $second_tenant_user = User::factory(['tenant_id' => 2])->create();
    $second_tenant_user->tenants()->attach(2);

    $this->actingAs($second_tenant_user);

    $second_tenant_user->assignRole('Support Agent');

    $data = [
        'name' => 'test',
        'user_id' => $second_tenant_user->id,
        'is_active' => true,
    ];

    $response = $this->post('/tokens', $data);
    $response->assertRedirect();

    $response = $this->get('/tokens');
    expect(count($this->get('api/tokens')->json()['data']))->toBe(1);
});
