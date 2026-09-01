<?php

namespace App\Rules;

use App\Models\Account;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Auth;

/**
 * Validates that the acting user is allowed to select the given account in the
 * quoting journey.
 *
 * CPQ-530: an account can be selected if the user is in no group, or the user
 * shares at least one group with the account. It cannot be selected if the user
 * is only in groups that the account is not in.
 */
class SelectableAccount implements ValidationRule
{
    /**
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $user = Auth::user();

        if (! $user || ! $user->isGrouped()) {
            return;
        }

        $account = Account::with('groups')->find($value);

        if (! $account) {
            return;
        }

        if (! $user->sharesGroupWith($account->groups->pluck('id'))) {
            $fail('You cannot select an account that is not in one of your groups.');
        }
    }
}
