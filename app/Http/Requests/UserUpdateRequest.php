<?php

namespace App\Http\Requests;

use App\Rules\AssignableGroups;
use Illuminate\Foundation\Http\FormRequest;

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
        $currentGroupIds = $target instanceof \App\Models\User ? $target->groupIds()->all() : [];

        return [
            'name' => ['string', 'max:255'],
            'email' => ['string', 'email', 'max:255'],
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
