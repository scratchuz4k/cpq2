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
        $parsed = Carbon::createFromFormat($this->format, $value);

        if (! $parsed) {
            $fail('Invalid date format.');
        }
        if (! ($parsed->format($this->format) === $value)) {
            $fail('Invalid date');
        }
    }
}
