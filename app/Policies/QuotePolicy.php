<?php

namespace App\Policies;

use App\Enums\QuoteStatusesEnum;
use App\Models\Quote;
use App\Models\User;

class QuotePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    private string $model = 'quote';

    private array $statusToBlockQuotes = [
        QuoteStatusesEnum::EXPIRED->name,
    ];

    public function viewAny(User $user): bool
    {
        if ($user->can("viewAny $this->model")) {
            return true;
        }

        if ($user->can("viewOwn $this->model")) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Quote $quote): bool
    {
        if (! $this->canManageQuote($user, $quote)) {
            return false;
        }

        if (! $user->can("view $this->model")) {
            return false;
        }

        if (in_array($quote->status, $this->statusToBlockQuotes)) {
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
    public function update(User $user, Quote $quote): bool
    {
        if (! $this->canManageQuote($user, $quote)) {
            return false;
        }

        if (! $user->can("update $this->model")) {
            return false;
        }

        if (in_array($quote->status, $this->statusToBlockQuotes)) {
            return false;
        }

        return true;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Quote $quote): bool
    {
        if (! $this->canManageQuote($user, $quote)) {
            return false;
        }

        if (! $user->can("delete $this->model")) {
            return false;
        }
        if (in_array($quote->status, $this->statusToBlockQuotes)) {
            return false;
        }

        return true;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Quote $quote): bool
    {
        if (! $user->can("restore $this->model")) {
            return false;
        }
        if (in_array($quote->status, $this->statusToBlockQuotes)) {
            return false;
        }

        return true;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Quote $quote): bool
    {
        if (! $user->can("forceDelete $this->model")) {
            return false;
        }
        if (in_array($quote->status, $this->statusToBlockQuotes)) {
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

    /*
     * Can logged user manage this quote
     */
    private function canManageQuote(User $user, Quote $quote): bool
    {
        $userAncestors = User::getChildAndOrphansUser()->get();

        return $user->id === $quote->user_id
            || $userAncestors->contains('id', $user->id);
    }
}
