<?php

namespace App\Http\Requests;

use App\Models\Account;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class GroupAccountCreateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * CPQ-530 "Additional Group Settings":
     *  - Ungrouped users may add any account to any group.
     *  - Grouped users may only move an account it already shares a group with
     *    into another of the groups they belong to.
     */
    public function authorize(): bool
    {
        $user = Auth::user();

        if (! $user || ! $user->isGrouped()) {
            return true;
        }

        if (! $user->groupIds()->contains((int) $this->input('group_id'))) {
            return false;
        }

        $account = Account::with('groups')->find($this->input('account_id'));

        return $account !== null && $user->sharesGroupWith($account->groups->pluck('id'));
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
                Rule::unique('group_account')->where(fn ($query) => $query->where('tenant_id', $user->tenant_id)
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
