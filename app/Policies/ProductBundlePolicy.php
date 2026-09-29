<?php

namespace App\Policies;

use App\Models\ProductBundle;
use App\Models\User;

class ProductBundlePolicy
{
    private string $model = 'product';

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
    public function view(User $user, ProductBundle $product): bool
    {
        if ($user->can("view $this->model")) {
            return true;
        }

        return false;
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
    public function update(User $user, ProductBundle $product): bool
    {
        if ($user->can("update $this->model")) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, ProductBundle $product): bool
    {
        if ($user->can("delete $this->model")) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, ProductBundle $product): bool
    {
        if ($user->can("restore $this->model")) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, ProductBundle $product): bool
    {
        if ($user->can("forceDelete $this->model")) {
            return true;
        }

        return false;
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
