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
                Rule::unique('accounts')->where(function ($query) use ($user) {
                    return $query->where('tenant_id', $user->tenant_id)
                        ->where('reference', request('reference'));
                })->ignore($account_id),
            ],
            'reference' => [
                Rule::unique('accounts')->where(function ($query) use ($user) {
                    return $query->where('tenant_id', $user->tenant_id)
                        ->where('name', request('name'));
                })->ignore($account_id),
            ],
            'contacts.*.name' => 'nullable',
            'contacts.*.email' => 'nullable|email',
            'is_active' => 'boolean',
        ];

    }
}
