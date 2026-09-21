<?php

namespace App\Policies;

use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class UserPolicy
{
    private string $model = 'user';

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
    public function view(User $user, User $model): bool
    {
        if ($user->tenant_id !== $model->tenant_id) {
            return false;
        }

        if (! $this->hasUserInChild($model)) {
            return false;
        }

        if (! $this->sharesGroup($user, $model)) {
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
     * Determine whether the user can import the model.
     */
    public function import(User $user): bool
    {
        if (! $user->can("import $this->model")) {
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
     * Determine if the given user can update the specified user.
     *
     * @param  User  $user  The authenticated user
     * @param  User  $model  The user model to be updated
     * @return bool
     */
    public function update(User $user, User $model)
    {
        if ($user->tenant_id !== $model->tenant_id) {
            return false;
        }

        if (! $this->hasUserInChild($model)) {
            return false;
        }

        if (! $this->sharesGroup($user, $model)) {
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
    public function delete(User $user, User $model): bool
    {

        if ($user->tenant_id !== $model->tenant_id) {
            return false;
        }
        if (! $this->hasUserInChild($model)) {
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
    public function restore(User $user, User $model): bool
    {

        if ($user->tenant_id !== $model->tenant_id) {
            return false;
        }
        if (! $this->hasUserInChild($user)) {
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
    public function forceDelete(User $user, User $model): bool
    {
        if ($user->tenant_id !== $model->tenant_id) {
            return false;
        }

        if (! $this->hasUserInChild($user)) {
            return false;
        }
        if (! $user->can("forceDelete $this->model")) {
            return false;
        }

        return true;
    }

    /**
     * CPQ-530: a grouped acting user may only see/manage users that share at
     * least one of their groups. Ungrouped users and Super Admin are unrestricted.
     */
    private function sharesGroup(User $user, User $model): bool
    {
        if (! $user->isGrouped() || $user->hasRole('Super Admin')) {
            return true;
        }

        return $user->sharesGroupWith($model->groupIds());
    }

    private function hasUserInChild(User $user): bool
    {
        if (Auth::check() && Auth::user()->roles()->withoutGlobalScopes()->where('name', 'Super Admin')->exists()) {
            return true; // Don't apply tenant filter for super admin
        }

        $first = Auth::user()->roles()->first();

        if ($first && ! $first->parent_id) {
            return true;
        }

        $child_roles = Role::descendants();

        $users = User::query()->whereHas('roles', function ($query) use ($child_roles) {
            $query->whereIn('id', $child_roles->pluck('id'));
        })->get();

        return $users->contains(fn ($child) => $child->id === $user->id);

    }
}
