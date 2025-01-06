<?php

namespace App\Policies;

use App\Models\Quote;
use App\Models\QuoteItem;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class QuoteItemPolicy
{
    private string $model = "quote_item";
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
    public function view(User $user, QuoteItem $quoteItem): bool
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
        if (!request()->has('quote_id')) {
            return false;
        }

        $quote = Quote::find(request('quote_id'));

        if ($user->id !== $quote->user_id) return false;

        if (!$user->can("create $this->model")) {
            return false;
        }
        return true;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, QuoteItem $quoteItem): bool
    {
        if ($user->id !== $quoteItem->quote->user_id) {
            return false;
        }

        if (!$user->can("update $this->model")) {
            return false;
        }

        return true;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, QuoteItem $quoteItem): bool
    {
        if ($user->id !== $quoteItem->quote->user_id) {
            return false;
        }

        if (!$user->can("delete $this->model")) {
            return false;
        }

        return true;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, QuoteItem $quoteItem): bool
    {
        if ($user->can("restore $this->model")) {
            return true;
        }
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, QuoteItem $quoteItem): bool
    {
        if ($user->can("forceDelete $this->model")) {
            return true;
        }
        return false;
    }
}
