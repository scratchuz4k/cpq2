<?php

namespace App\Rules;

use Closure;
use Illuminate\Support\Facades\DB;
use Illuminate\Contracts\Validation\ValidationRule;

class ExistsInDatabase implements ValidationRule
{
    protected $table;
    protected $column;

    /**
     * Create a new rule instance.
     *
     * @param  string  $table  The table to check against
     * @param  string  $column The column to check against
     * @return void
     */
    public function __construct($table, $column)
    {
        $this->table = $table;
        $this->column = $column;
    }

    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // $value is the array of keys to validate
        $keys = collect($value);

        // Check if all keys exist in the specified table and column
        $count = DB::table($this->table)
            ->whereIn($this->column, $keys)
            ->count();

        if ($count !== $keys->count()) {
            $fail('One or more keys do not exist in the specified table.');
        }
    }
}
