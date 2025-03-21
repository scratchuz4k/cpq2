<?php

namespace App\Http\Requests;

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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $user = Auth::user();
        $account_id = null;
        if ($this->route() && array_key_exists('account', $this->route()->parameters())) {
            $account_id = $this->route()->parameters()['account']->id;
        }

        return [
            'name' => 'string',
            'reference' => [
                'string',
                Rule::unique('accounts')
                    ->where(
                        'tenant_id',
                        $user->tenant_id
                    )
                    ->ignore($account_id),
            ],
            'contacts.*.name' => 'nullable',
            'contacts.*.email' => 'nullable|email',
        ];
    }
}
