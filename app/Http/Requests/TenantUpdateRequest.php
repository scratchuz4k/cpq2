<?php

namespace App\Http\Requests;

use App\Rules\XmlValidator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\File;

class TenantUpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'sso_id' => ['nullable', new XmlValidator],
            'sso_text_area' => ['nullable', new XmlValidator]
        ];
    }
}
