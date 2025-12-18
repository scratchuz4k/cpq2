<?php

namespace App\Rules;

use App\Models\Tenant;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\DB;

class TenantUniqueRule implements ValidationRule
{
    public function __construct(
        protected string $table,
        protected string $column,
        protected ?int $ignoreId = null
    ) {}

    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $tenant = Tenant::current();

        $query = DB::table($this->table)
            ->where($this->column, $value)
            ->where('tenant_id', $tenant->id);

        if ($this->ignoreId !== null) {
            $query->where('id', '<>', $this->ignoreId);
        }

        if ($query->exists()) {
            $fail("The {$attribute} has already been taken.");
        }
    }
}
