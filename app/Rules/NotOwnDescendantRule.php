<?php

namespace App\Rules;

use App\Models\Role;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class NotOwnDescendantRule implements ValidationRule
{
    public function __construct(
        protected Role $role
    ) {}

    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ((int) $value === $this->role->id) {
            $fail('A role cannot be its own parent.');

            return;
        }

        if (in_array((int) $value, $this->role->getDescendantIds(), true)) {
            $fail('The selected parent would create a circular role hierarchy.');
        }
    }
}
