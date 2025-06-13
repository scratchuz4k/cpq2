<?php

namespace App\Policies;

use App\Models\Account;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class AccountPolicy
{
    private string $model = "account";

    private function isAccountManagementRestricted(User $user): bool
    {
        $configs = $user->currentTenant->configs ?? [];
        return !empty($configs['restrict_account_management_is_active']) && $configs['restrict_account_management_is_active'] !== '0';
    }

    public function isAccountInUserGroup(User $user, Account $account)
    {
        if (!$user->group()->exists()) {
            return true;
        }

        if ($user->group()->exists() && $user->group->accounts->contains('id', $account->id)) {
            return true;
        }
        return false;
    }

    private function canManageAccount(User $user, string $action): bool
    {
        if ($user->can("$action $this->model") && !$this->isAccountManagementRestricted($user)) {
            return true;
        }

        if ($user->can("view token") && request()->expectsJson()) {
            return true;
        }
        return false;
    }



    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        if (!$user->can("viewAny $this->model")) {
            return false;
        }
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Account $account): bool
    {
        if (!$user->can("view $this->model") && $this->isAccountInUserGroup($user, $account)) {
            return false;
        }
        return true;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $this->canManageAccount($user, 'create');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Account $account): bool
    {
        return $this->canManageAccount($user, 'update') && $this->isAccountInUserGroup($user, $account);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Account $account): bool
    {
        return $this->canManageAccount($user, 'delete') && $this->isAccountInUserGroup($user, $account);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Account $account): bool
    {
        return $this->canManageAccount($user, 'restore') && $this->isAccountInUserGroup($user, $account);
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Account $account): bool
    {
        return $this->canManageAccount($user, 'forceDelete') && $this->isAccountInUserGroup($user, $account);
    }
}
