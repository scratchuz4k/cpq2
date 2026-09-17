<?php

namespace App\Policies;

use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class RolePolicy
{
    private string $model = 'role';

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        if ($user->can("viewAny $this->model")) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Role $role): bool
    {
        if ($user->tenant_id !== $role->tenant_id) {
            return false;
        }

        if (! $this->hasRoleInChild($role)) {
            return false;
        }

        if (! $user->can("view $this->model")) {
            return false;
        }

        return true;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        if ($user->can("create $this->model")) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Role $role): bool
    {

        if ($user->tenant_id !== $role->tenant_id) {
            return false;
        }

        if (! $this->hasRoleInChild($role)) {
            return false;
        }

        if (! $user->can("update $this->model")) {
            return false;
        }

        return true;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Role $role): bool
    {

        if ($user->tenant_id !== $role->tenant_id) {
            return false;
        }

        if (! $this->hasRoleInChild($role)) {
            return false;
        }

        if (! $user->can("delete $this->model")) {
            return false;
        }

        return true;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Role $role): bool
    {

        if ($user->tenant_id !== $role->tenant_id) {
            return false;
        }

        if (! $this->hasRoleInChild($role)) {
            return false;
        }

        if (! $user->can("restore $this->model")) {
            return false;
        }

        return true;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Role $role): bool
    {
        if ($user->tenant_id !== $role->tenant_id) {
            return false;
        }

        if (! $this->hasRoleInChild($role)) {
            return false;
        }

        if (! $user->can("forceDelete $this->model")) {
            return false;
        }

        return true;
    }

    /**
     * Determine whether the user can export the model.
     */
    public function export(User $user): bool
    {
        if (! $user->can("export $this->model")) {
            return false;
        }

        return true;
    }

    /**
     * Determine whether the user can import the model.
     */
    public function import(User $user): bool
    {
        if (! $user->can("import $this->model")) {
            return false;
        }

        return true;
    }

    private function hasRoleInChild(Role $role): bool
    {
        if (Auth::check() && Auth::user()->roles()->withoutGlobalScopes()->where('roles.name', 'Super Admin')->exists()) {
            return true; // Don't apply tenant filter for super admin
        }

        $first = Auth::user()->roles()->first();

        if ($first && ! $first->parent_id) {
            return true;
        }

        $child_roles = Role::descendants();

        return $child_roles->contains(fn ($child) => $child->id === $role->id);

    }
}
