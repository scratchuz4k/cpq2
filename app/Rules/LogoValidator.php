<?php

namespace App\Rules;

use App\Models\Resource;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class LogoValidator implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $allowed_logo_formats = [
            'image/jpeg',
            'image/jpg',
            'image/png',
            'image/gif',
        ];

        $res = Resource::findOrFail($value);

        if (! in_array($res->mime_type, $allowed_logo_formats)) {
            $fail('The :attribute be of type jpeg, jpg, png or gif.');
        }
    }
}
