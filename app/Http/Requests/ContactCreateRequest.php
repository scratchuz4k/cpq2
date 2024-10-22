<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ContactCreateRequest extends FormRequest
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
            'account_id' => 'required',
            'email' => 'nullable|email',
            'primary_contact_name' => 'nullable|string',
            'address_1' => 'nullable|string',
            'city' => 'nullable|string',
            'postcode' => 'nullable|string',
        ];
    }
}
