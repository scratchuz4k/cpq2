<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class GroupUserCreateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * CPQ-530 "Additional Group Settings":
     *  - Ungrouped users may add any user to any group.
     *  - Grouped users may only move a user they already share a group with
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

        $target = User::with('groups')->find($this->input('user_id'));

        return $target !== null && $user->sharesGroupWith($target->groups->pluck('id'));
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
            'user_id' => [
                'required',
                Rule::unique('group_user')->where(fn ($query) => $query->where('tenant_id', $user->tenant_id)
                    ->where('group_id', request('group_id'))),
            ],
        ];
    }

    public function messages()
    {
        return [
            'user_id' => 'User is already related to that group',
        ];
    }
}
