<?php

namespace App\Rules;

use App\Models\Resource;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class XmlValidator implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (is_null($value)) {
            return;
        }

        if (!simplexml_load_string((string) $value, null, LIBXML_NOERROR)) {
            $fail('The :attribute must contain a valid XML.');
        }
    }
}
