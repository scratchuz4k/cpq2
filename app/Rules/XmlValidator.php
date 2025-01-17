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
        // Find resource and get file content
        if (is_int($value)) {
            $res = Resource::findOrFail($value);
            $xml_content = Storage::disk('public')->get($res->getRouteValue());
        }

        if (is_string($value)) {
            $xml_content = $value;
        }

        if (!simplexml_load_string($xml_content, null, LIBXML_NOERROR )) {
            $fail('The :attribute must contain a valid XML.');
        }
    }
}
