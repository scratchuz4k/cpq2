<?php

namespace App\Rules;

use App\Models\Resource;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class FileValidator implements ValidationRule
{
    private $allowed_logo_formats = [
        'xml' => [],
        'image' => [
            'image/jpeg',
            'image/jpg',
            'image/png',
            'image/gif'
        ],
    ];

    public function __construct(private $filetype) {}
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {

        $res = Resource::find(id: $value);

        if (!$res) {
            return;
        }

        if (! array_key_exists($this->filetype, $this->allowed_logo_formats)) {
            $fail('The file typ attribute is not present.');
        }

        if (! in_array($res->mime_type, $this->allowed_logo_formats)) {
            $fail('The :attribute be of type jpeg, jpg, png or gif.');
        }
    }
}
