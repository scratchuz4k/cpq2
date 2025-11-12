<?php

namespace App\Rules;

use App\Models\Tenant;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\DB;

class TenantExistsRule implements ValidationRule
{
    public function __construct(
        protected string $table
    ) {}

    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $tenant = Tenant::current();

        $exists = DB::table($this->table)
            ->where('id', $value)
            ->where('tenant_id', $tenant->id)
            ->exists();

        if (! $exists) {
            $fail("The selected {$attribute} doesn't exist.");
        }
    }
}
