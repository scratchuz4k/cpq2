<?php

namespace App\Http\Requests;

use App\Models\User;
use App\Rules\AssignableGroups;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UserUpdateRequest extends FormRequest
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
        $target = $this->route('user');
        $currentGroupIds = $target instanceof User ? $target->groupIds()->all() : [];
        $targetId = $target instanceof User ? $target->id : $target;

        return [
            // `sometimes` so partial updates (e.g. the group page sending only `groups`)
            // are not forced to resend name/email.
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'email' => [
                'sometimes',
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($targetId),
            ],
            'parent_id' => [
                'nullable',
                'integer',
                function ($attribute, $value, $fail) {
                    // Only fail if there is an 'id' in the request
                    if ($value == $this->user->id) {
                        $fail('The parent_id cannot be the same as the user id.');
                    }
                },
            ],
            'groups' => ['nullable', 'array', new AssignableGroups($currentGroupIds)],
            'groups.*' => ['integer', 'exists:groups,id'],
        ];
    }
}
