<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class GroupAccountCreateRequest extends FormRequest
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
            'group_id' => ['required'],
            'account_id' => [
                'required',
                Rule::unique('group_account')->where(fn($query) => $query->where('tenant_id', $user->tenant_id)
                    ->where('group_id', request('group_id'))),
            ],
        ];
    }

    public function messages()
    {
        return [
            'account_id' => 'Account is already related to that group',
        ];
    }
}
