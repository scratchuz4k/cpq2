<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Auth;

/**
 * Validates the `groups` array submitted when creating/editing an Account or a User.
 *
 * CPQ-530: a grouped acting user may only add groups they belong to. Groups the
 * record is already in that the actor does not belong to may stay (they are
 * preserved), so they are allowed through here too. Ungrouped acting users may
 * assign anything.
 *
 * The "must stay in at least one group" and merge-preserve behaviour is enforced
 * in {@see \App\Support\GroupAssignment} during the sync.
 */
class AssignableGroups implements ValidationRule
{
    /**
     * @param  iterable<int>  $retainableIds  group ids the record already belongs to
     */
    public function __construct(
        protected iterable $retainableIds = [],
    ) {}

    /**
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $user = Auth::user();

        if (! $user || ! $user->isGrouped()) {
            return;
        }

        $allowed = $user->groupIds()->merge(collect($this->retainableIds)->map(fn ($id) => (int) $id));

        $outside = collect(is_array($value) ? $value : [])
            ->map(fn ($id) => (int) $id)
            ->filter()
            ->unique()
            ->diff($allowed);

        if ($outside->isNotEmpty()) {
            $fail('You can only assign groups that you belong to.');
        }
    }
}
