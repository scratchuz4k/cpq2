<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\DB;

class TenantExistsRule implements ValidationRule
{
    public function __construct(
        protected string $table,
        protected ?int $tenantId = null
    ) {}

    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $tenantId = $this->tenantId;

        $exists = DB::table($this->table)
            ->where('id', $value)
            ->where('tenant_id', $tenantId)
            ->exists();

        if (! $exists) {
            $fail("The selected {$attribute} doesn't exist for this tenant.");
        }
    }
}
