<?php

namespace App\Services;

use App\Imports\UsersImport;
use App\Models\User;
use App\Support\GroupAssignment;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Facades\Excel;

class UsersService
{
    public function store(array $array): User
    {
        if (array_key_exists('password', $array) && ! empty($array['password'])) {
            $array['password'] = Hash::make($array['password']);
        }

        if (array_key_exists('parent_id', $array) && $array['parent_id'] == 0) {
            $array['parent_id'] = null;
        }

        $user = User::create($array);

        if (array_key_exists('role_id', $array) && ! empty($array['role_id'])) {
            $user->assignRole($array['role_id']);
        }

        $this->syncGroups($user, $array, allowFloor: true);

        if ($user->tenant_id) {
            $user->tenants()->sync($user->tenant_id);
        }

        return $user;
    }

    public function update(User $user, array $array): User
    {
        if (array_key_exists('password', $array) && ! empty($array['password'])) {
            $array['password'] = Hash::make($array['password']);
        } else {
            $array['password'] = $user->password;
        }

        $user->update($array);

        $this->syncGroups($user, $array);

        if (array_key_exists('role_id', $array)) {
            $user->syncRoles($array['role_id']);
        }

        if ($user->tenant_id) {
            $user->tenants()->sync($user->tenant_id);
        }

        return $user;
    }

    public function import(array $array)
    {
        $import = new UsersImport;
        Excel::import($import, $array['file']);

        return $import->results;
    }

    /**
     * Apply the CPQ-530 group-assignment rules when a user's groups change.
     *
     * On create ($allowFloor), a grouped actor that submits no groups gets the
     * new user floored to their own groups. On update we only touch memberships
     * when `groups` is explicitly supplied.
     */
    protected function syncGroups(User $user, array $array, bool $allowFloor = false): void
    {
        $actor = Auth::user();
        $hasKey = array_key_exists('groups', $array);

        if (! $hasKey && ! ($allowFloor && $actor && $actor->isGrouped())) {
            return;
        }

        $submitted = $hasKey ? (array) ($array['groups'] ?? []) : null;

        $groupIds = GroupAssignment::resolve(
            $actor,
            $user->groups()->pluck('groups.id'),
            $submitted,
            'user',
        );

        $user->groups()->sync(
            collect($groupIds)->mapWithKeys(fn ($id) => [$id => ['tenant_id' => $actor->tenant_id]])->all()
        );
    }
}
