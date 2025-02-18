<?php

namespace App\Policies;

use App\Models\SolutionType;
use App\Models\User;

class SolutionTypePolicy
{

    private string $model = "solution_type";
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
    public function view(User $user, SolutionType $solutionType): bool
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
    public function update(User $user, SolutionType $solutionType): bool
    {
        $tenant = $user->currentTenant->refresh();
        if (
            $user->can("update $this->model")
            && $tenant->solutionTypes->contains($solutionType)
        ) {
            return true;
        }
        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, SolutionType $solutionType): bool
    {
        $tenant = $user->currentTenant->refresh();
        if (
            $user->can("delete $this->model")
            && $tenant->solutionTypes->contains($solutionType)
        ) {
            return true;
        }
        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, SolutionType $solutionType): bool
    {
        if ($user->can("restore $this->model")) {
            return true;
        }
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, SolutionType $solutionType): bool
    {
        if ($user->can("forceDelete $this->model")) {
            return true;
        }
        return false;
    }
}
