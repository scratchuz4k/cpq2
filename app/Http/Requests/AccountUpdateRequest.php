<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class AccountUpdateRequest extends FormRequest
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
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $user = Auth::user();
        $account_id = null;
        if ($this->route() && array_key_exists('account', $this->route()->parameters())) {
            $account_id = $this->route()->parameters()['account']->id;
        }

        return [
            'name' => [
                'required',
                Rule::unique('accounts')->where(fn ($query) => $query->where('tenant_id', $user->tenant_id)
                    ->where('reference', request('reference')))->ignore($account_id),
            ],
            'reference' => [
                'required',
                Rule::unique('accounts')->where(fn ($query) => $query->where('tenant_id', $user->tenant_id)
                    ->where('name', request('name')))->ignore($account_id),
            ],
            'contacts.*.name' => 'nullable',
            'contacts.*.email' => 'nullable|email',
            'contacts.*.phone' => 'nullable|max_digits:12',
            'is_active' => 'boolean',
            'groups' => 'nullable|array',
            'groups.*' => 'integer|exists:groups,id',
        ];

    }

    public function attributes(): array
    {
        $attributes = [];

        foreach ($this->input('contacts', []) as $index => $contact) {
            $attributes["contacts.{$index}.phone"] = $index + 1;
            $attributes["contacts.{$index}.email"] = $index + 1;
        }

        return $attributes;
    }

    public function messages(): array
    {
        return [
            'contacts.*.phone.max_digits' => 'The contact #:attribute phone number must not exceed :max digits.',
            'contacts.*.email.email' => 'The contact #:attribute email must be a valid email address',
        ];
    }
}
