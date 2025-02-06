<?php

namespace App\Rules;

use App\Models\Resource;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class FileValidator implements ValidationRule
{
    private $allowed_logo_formats = [
        'xml' => [
            'text/xml',
            'application/xml'
        ],
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
            $fail('The file type attribute is not present.');
        }

        if (! in_array($res->mime_type, $this->allowed_logo_formats[$this->filetype])) {
            $extensions = implode(', ', array_unique(array_map(
                fn($mime) => '.' . explode('/', $mime)[1] ?? '',
                $this->allowed_logo_formats[$this->filetype]
            )));
            $fail('The file must be of type ' . $extensions . '.');
        }
    }
}
