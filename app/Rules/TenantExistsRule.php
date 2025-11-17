<?php

namespace App\Rules;

use App\Models\Tenant;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

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
            if (strpos($attribute, '.')) {
                $attrs = explode('.', $attribute);

                $fail(
                    'The '.Str::singular($attrs[0]).' with ID '.$value.' doesn\'t exist.'
                );
            } else {
                $fail("The selected {$attribute} doesn't exist.");
            }
        }
    }
}
