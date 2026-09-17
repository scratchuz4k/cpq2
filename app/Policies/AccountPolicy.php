<?php

namespace App\Policies;

use App\Models\Account;
use App\Models\User;

class AccountPolicy
{
    private string $model = 'account';

    public function userCanManageAccount(User $user, Account $account): bool
    {
        // CPQ-530: ungrouped users (and Super Admin) are unrestricted; grouped
        // users may only manage accounts sharing one of their groups.
        if (! $user->isGrouped() || $user->hasRole('Super Admin')) {
            return true;
        }

        return $user->sharesGroupWith($account->groups()->pluck('groups.id'));
    }

    private function hasPermission(User $user, string $action): bool
    {
        if ($user->can("$action $this->model")) {
            return true;
        }

        if ($user->can('view token') && request()->expectsJson()) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        if (! $user->can("viewAny $this->model")) {
            return false;
        }

        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Account $account): bool
    {
        return $this->hasPermission($user, 'view') && $this->userCanManageAccount($user, $account);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $this->hasPermission($user, 'create');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Account $account): bool
    {
        return $this->hasPermission($user, 'update') && $this->userCanManageAccount($user, $account);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Account $account): bool
    {
        return $this->hasPermission($user, 'delete') && $this->userCanManageAccount($user, $account);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Account $account): bool
    {
        return $this->hasPermission($user, 'restore') && $this->userCanManageAccount($user, $account);
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Account $account): bool
    {
        return $this->hasPermission($user, 'forceDelete') && $this->userCanManageAccount($user, $account);
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
}
