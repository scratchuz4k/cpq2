<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Http\UploadedFile;

class XmlValidator implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {

        if ($value instanceof UploadedFile) {
            $xml_content = file_get_contents($value);
        }

        if (is_string($value)) {
            $xml_content = $value;
        }

        if (!simplexml_load_string($xml_content, null, LIBXML_NOERROR )) {
            $fail('The :attribute must contain a valid XML.');
        }
    }
}
