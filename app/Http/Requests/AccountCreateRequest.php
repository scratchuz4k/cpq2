<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class AccountCreateRequest extends FormRequest
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
        $user = Auth::user();
        return [
            'name' => 'required',
            'reference' => [
                'required',
                Rule::unique('accounts')
                    ->where(
                        'tenant_id',
                        $user->tenant_id
                    )
            ],
            'contacts.*.name' => 'nullable',
            'contacts.*.email' => 'nullable|email'
        ];
    }
}
