<?php

namespace App\Rules;

use Carbon\Carbon;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ValidDateValidator implements ValidationRule
{
    public function __construct(private string $format = 'd/m/Y')
    {
        $this->format = $format;
    }

    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (empty($value)) {
            return;
        }

        $parsed = Carbon::createFromFormat($this->format, $value);

        if (! $parsed) {
            $fail("The {$attribute} field contains a date in the wrong format '{$value}'. The expected format is '{$this->format}'");
        }
        if (! ($parsed->format($this->format) === $value)) {
            $fail("The {$attribute} field contains an invalid date '{$value}'");
        }
    }
}
