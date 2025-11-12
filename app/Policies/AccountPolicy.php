<?php

namespace App\Policies;

use App\Models\Account;
use App\Models\User;

class AccountPolicy
{
    private string $model = 'account';

    private function isAccountManagementRestricted(User $user): bool
    {
        $configs = $user->currentTenant->configs ?? [];

        return ! empty($configs['restrict_account_management_is_active']) && $configs['restrict_account_management_is_active'] !== '0';
    }

    public function userCanManageAccount(User $user, Account $account)
    {
        /* bug fix */
        if (! $user->group()) {
            return true;
        }

        if (! $user->hasRole('Super Admin')) {
            return $user->group->accounts->contains('id', $account->id);
        }

        return true;
    }

    private function hasPermission(User $user, string $action): bool
    {
        if ($user->can("$action $this->model") && ! $this->isAccountManagementRestricted($user)) {
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
        if (! $user->can("view $this->model") && $this->userCanManageAccount($user, $account)) {
            return false;
        }

        return true;
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
}
