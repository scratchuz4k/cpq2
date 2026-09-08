<?php

use App\Models\Account;
use App\Models\Group;
use App\Models\GroupAccount;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->artisan('db:seed');
    setTenantForPermissions();

    $this->tenant = Tenant::find(1);

    $this->groupA = Group::factory()->create(['tenant_id' => $this->tenant->id, 'name' => 'Group A']);
    $this->groupB = Group::factory()->create(['tenant_id' => $this->tenant->id, 'name' => 'Group B']);
    $this->groupC = Group::factory()->create(['tenant_id' => $this->tenant->id, 'name' => 'Group C']);

    $this->makeUser = function (array $groupIds = []): User {
        $user = User::factory()->create(['tenant_id' => $this->tenant->id]);
        $user->tenants()->attach($this->tenant->id);
        $user->assignRole('Company Admin');
        foreach ($groupIds as $id) {
            $user->groups()->attach($id, ['tenant_id' => $this->tenant->id]);
        }

        return $user->fresh();
    };

    $this->login = function (User $user) {
        Auth::guard('web')->setUser($user);
        $this->actingAs($user);
    };
});

/*
|--------------------------------------------------------------------------
| Account create / edit
|--------------------------------------------------------------------------
*/

it('lets an ungrouped user create an account with no groups', function () {
    ($this->login)(($this->makeUser)());

    $this->post('accounts', ['name' => 'Acme', 'reference' => 'ACME1'])->assertRedirect();

    expect(Account::where('name', 'Acme')->first()->groups()->count())->toBe(0);
});

it('rejects a grouped user assigning a group they do not belong to', function () {
    ($this->login)(($this->makeUser)([$this->groupA->id]));

    $this->postJson('api/accounts', [
        'name' => 'Beta',
        'reference' => 'BETA1',
        'groups' => [$this->groupA->id, $this->groupB->id],
    ])->assertStatus(422);
});

it('lets a grouped user assign a subset of their own groups', function () {
    ($this->login)(($this->makeUser)([$this->groupA->id, $this->groupB->id]));

    $this->postJson('api/accounts', [
        'name' => 'Gamma',
        'reference' => 'GAMMA1',
        'groups' => [$this->groupA->id],
    ])->assertSuccessful();

    expect(Account::where('name', 'Gamma')->first()->groups->pluck('id')->all())
        ->toBe([$this->groupA->id]);
});

it('defaults a grouped user\'s new account to all of their groups when none are submitted', function () {
    ($this->login)(($this->makeUser)([$this->groupA->id, $this->groupB->id]));

    $this->post('accounts', ['name' => 'Delta', 'reference' => 'DELTA1'])->assertRedirect();

    expect(Account::where('name', 'Delta')->first()->groups->pluck('id')->sort()->values()->all())
        ->toBe(collect([$this->groupA->id, $this->groupB->id])->sort()->values()->all());
});

it('preserves account groups the editing user does not belong to', function () {
    $account = Account::factory()->create(['tenant_id' => $this->tenant->id]);
    $account->groups()->attach([$this->groupA->id, $this->groupC->id], ['tenant_id' => $this->tenant->id]);

    // Editor is only in A and B — editing with just [B] must keep C and drop A.
    ($this->login)(($this->makeUser)([$this->groupA->id, $this->groupB->id]));

    $this->putJson("api/accounts/{$account->id}", [
        'name' => $account->name,
        'reference' => $account->reference,
        'groups' => [$this->groupB->id],
    ])->assertSuccessful();

    expect($account->fresh()->groups->pluck('id')->sort()->values()->all())
        ->toBe(collect([$this->groupB->id, $this->groupC->id])->sort()->values()->all());
});

/*
|--------------------------------------------------------------------------
| User create
|--------------------------------------------------------------------------
*/

it('rejects a grouped user creating another user in a group they do not belong to', function () {
    ($this->login)(($this->makeUser)([$this->groupA->id]));

    $this->postJson('api/users', [
        'name' => 'New User',
        'email' => 'new@example.test',
        'password' => 'password123',
        'groups' => [$this->groupB->id],
    ])->assertStatus(422);
});

it('rejects a grouped user creating a user with an explicitly empty group list', function () {
    ($this->login)(($this->makeUser)([$this->groupA->id, $this->groupB->id]));

    $this->postJson('api/users', [
        'name' => 'New User',
        'email' => 'empty-groups@example.test',
        'password' => 'password123',
        'groups' => [],
    ])->assertStatus(422)->assertJsonValidationErrorFor('groups');

    expect(User::where('email', 'empty-groups@example.test')->exists())->toBeFalse();
});

it('floors a new user to the creator\'s groups when none are submitted', function () {
    ($this->login)(($this->makeUser)([$this->groupA->id, $this->groupB->id]));

    $this->post('users', [
        'name' => 'Child User',
        'email' => 'child@example.test',
        'password' => 'password123',
        'role_id' => 5,
        'parent_id' => 0,
    ])->assertRedirect();

    expect(User::where('email', 'child@example.test')->first()->groups->pluck('id')->sort()->values()->all())
        ->toBe(collect([$this->groupA->id, $this->groupB->id])->sort()->values()->all());
});

it('removes a user from the editor\'s group while preserving groups the editor is not in', function () {
    $target = ($this->makeUser)([$this->groupA->id, $this->groupC->id]);

    // Editor in A only. Frontend re-sends the full membership minus A.
    ($this->login)(($this->makeUser)([$this->groupA->id]));

    $this->putJson("api/users/{$target->id}", [
        'groups' => [$this->groupC->id],
    ])->assertSuccessful();

    expect($target->fresh()->groups->pluck('id')->all())->toBe([$this->groupC->id]);
});

it('lists a grouped editor\'s own groups only by default', function () {
    ($this->login)(($this->makeUser)([$this->groupA->id]));

    $ids = collect($this->getJson('api/groups')->json('data'))->pluck('id')->all();

    expect($ids)->toBe([$this->groupA->id]);
});

it('also lists the edited user\'s groups when for_user_id is supplied', function () {
    $target = ($this->makeUser)([$this->groupA->id, $this->groupB->id]);
    ($this->login)(($this->makeUser)([$this->groupA->id]));

    $ids = collect($this->getJson("api/groups?for_user_id={$target->id}")->json('data'))
        ->pluck('id')->sort()->values()->all();

    expect($ids)->toBe(collect([$this->groupA->id, $this->groupB->id])->sort()->values()->all());
});

it('ignores for_user_id for a user the editor may not manage', function () {
    $stranger = ($this->makeUser)([$this->groupC->id]);
    ($this->login)(($this->makeUser)([$this->groupA->id]));

    $ids = collect($this->getJson("api/groups?for_user_id={$stranger->id}")->json('data'))->pluck('id')->all();

    expect($ids)->toBe([$this->groupA->id]);
});

it('also lists an edited account\'s groups when for_account_id is supplied', function () {
    $account = Account::factory()->create(['tenant_id' => $this->tenant->id]);
    $account->groups()->attach([$this->groupA->id, $this->groupB->id], ['tenant_id' => $this->tenant->id]);
    ($this->login)(($this->makeUser)([$this->groupA->id]));

    $ids = collect($this->getJson("api/groups?for_account_id={$account->id}")->json('data'))
        ->pluck('id')->sort()->values()->all();

    expect($ids)->toBe(collect([$this->groupA->id, $this->groupB->id])->sort()->values()->all());
});

it('ignores for_account_id for an account the editor may not manage', function () {
    $account = Account::factory()->create(['tenant_id' => $this->tenant->id]);
    $account->groups()->attach([$this->groupC->id], ['tenant_id' => $this->tenant->id]);
    ($this->login)(($this->makeUser)([$this->groupA->id]));

    $ids = collect($this->getJson("api/groups?for_account_id={$account->id}")->json('data'))->pluck('id')->all();

    expect($ids)->toBe([$this->groupA->id]);
});

it('rejects updating a user to an email already used by another user', function () {
    $other = ($this->makeUser)();
    $other->update(['email' => 'taken@example.test']);

    $target = ($this->makeUser)();

    ($this->login)(($this->makeUser)());

    $this->putJson("api/users/{$target->id}", [
        'name' => 'Renamed',
        'email' => 'taken@example.test',
    ])->assertStatus(422)->assertJsonValidationErrorFor('email');

    expect($target->fresh()->email)->not->toBe('taken@example.test');
});

it('allows updating a user while keeping their own email', function () {
    $target = ($this->makeUser)();
    $target->update(['email' => 'keep@example.test']);

    ($this->login)(($this->makeUser)());

    $this->putJson("api/users/{$target->id}", [
        'name' => 'Renamed',
        'email' => 'keep@example.test',
    ])->assertSuccessful();

    expect($target->fresh()->name)->toBe('Renamed');
});

it('rejects a grouped user removing every group from their own account', function () {
    $self = ($this->makeUser)([$this->groupA->id, $this->groupB->id]);
    ($this->login)($self);

    $this->putJson("api/users/{$self->id}", [
        'groups' => [],
    ])
        ->assertStatus(422)
        ->assertJsonValidationErrorFor('groups')
        ->assertJsonFragment([
            'groups' => ['You cannot remove all groups from your account. Please contact a company administrator'],
        ]);

    expect($self->fresh()->groups->pluck('id')->sort()->values()->all())
        ->toBe(collect([$this->groupA->id, $this->groupB->id])->sort()->values()->all());
});

/*
|--------------------------------------------------------------------------
| Group page — add / remove
|--------------------------------------------------------------------------
*/

it('forbids a single-group user adding an account to their group', function () {
    $account = Account::factory()->create(['tenant_id' => $this->tenant->id]);

    ($this->login)(($this->makeUser)([$this->groupA->id]));

    $this->postJson('api/group-accounts', [
        'account_id' => $account->id,
        'group_id' => $this->groupA->id,
    ])->assertForbidden();
});

it('lets a multi-group user move an account between their groups', function () {
    $account = Account::factory()->create(['tenant_id' => $this->tenant->id]);
    $account->groups()->attach($this->groupA->id, ['tenant_id' => $this->tenant->id]);

    ($this->login)(($this->makeUser)([$this->groupA->id, $this->groupB->id]));

    $this->postJson('api/group-accounts', [
        'account_id' => $account->id,
        'group_id' => $this->groupB->id,
    ])->assertSuccessful();

    expect($account->fresh()->groups->pluck('id')->sort()->values()->all())
        ->toBe(collect([$this->groupA->id, $this->groupB->id])->sort()->values()->all());
});

it('forbids a grouped user removing an account from a group they are not in', function () {
    $account = Account::factory()->create(['tenant_id' => $this->tenant->id]);
    $account->groups()->attach($this->groupC->id, ['tenant_id' => $this->tenant->id]);
    $pivot = GroupAccount::where('account_id', $account->id)->where('group_id', $this->groupC->id)->first();

    ($this->login)(($this->makeUser)([$this->groupA->id, $this->groupB->id]));

    $this->deleteJson("api/group-accounts/{$pivot->id}")->assertForbidden();
});

it('lets an ungrouped user remove an account from any group', function () {
    $account = Account::factory()->create(['tenant_id' => $this->tenant->id]);
    $account->groups()->attach($this->groupC->id, ['tenant_id' => $this->tenant->id]);
    $pivot = GroupAccount::where('account_id', $account->id)->where('group_id', $this->groupC->id)->first();

    ($this->login)(($this->makeUser)());

    $this->deleteJson("api/group-accounts/{$pivot->id}")->assertSuccessful();

    expect($account->fresh()->groups()->count())->toBe(0);
});

/*
|--------------------------------------------------------------------------
| Quoting journey — account selection
|--------------------------------------------------------------------------
*/

it('rejects selecting an account outside the user\'s groups when creating a quote', function () {
    $account = Account::factory()->create(['tenant_id' => $this->tenant->id]);
    $account->groups()->attach($this->groupC->id, ['tenant_id' => $this->tenant->id]);

    ($this->login)(($this->makeUser)([$this->groupA->id]));

    $this->postJson('api/quotes', [
        'account_id' => $account->id,
        'solution_type_id' => 1,
    ])->assertStatus(422);
});

/*
|--------------------------------------------------------------------------
| Created Quotes page — visibility
|--------------------------------------------------------------------------
*/

it('hides a quote whose account is outside the viewer\'s groups', function () {
    // AC example: user A in {A,B}, user B in {A}, account C in {B}.
    $userA = ($this->makeUser)([$this->groupA->id, $this->groupB->id]);
    $userB = ($this->makeUser)([$this->groupA->id]);

    $accountC = Account::factory()->create(['tenant_id' => $this->tenant->id]);
    $accountC->groups()->attach($this->groupB->id, ['tenant_id' => $this->tenant->id]);

    ($this->login)($userA);
    $created = $this->postJson('api/quotes', [
        'account_id' => $accountC->id,
        'solution_type_id' => 1,
    ])->assertSuccessful()->json();

    ($this->login)($userB);

    $ids = collect($this->getJson('api/quotes')->json('data'))->pluck('id');
    expect($ids)->not->toContain($created['id']);

    $this->get("quotes/{$created['id']}/edit")->assertForbidden();
})->group('quote-visibility');

it('shows a quote when the viewer shares a group with both creator and account', function () {
    $userA = ($this->makeUser)([$this->groupA->id, $this->groupB->id]);
    $userB = ($this->makeUser)([$this->groupB->id]);

    $accountC = Account::factory()->create(['tenant_id' => $this->tenant->id]);
    $accountC->groups()->attach($this->groupB->id, ['tenant_id' => $this->tenant->id]);

    ($this->login)($userA);
    $created = $this->postJson('api/quotes', [
        'account_id' => $accountC->id,
        'solution_type_id' => 1,
    ])->assertSuccessful()->json();

    ($this->login)($userB);

    $ids = collect($this->getJson('api/quotes')->json('data'))->pluck('id');
    expect($ids)->toContain($created['id']);
})->group('quote-visibility');
