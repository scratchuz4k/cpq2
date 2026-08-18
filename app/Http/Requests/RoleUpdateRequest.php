<?php

namespace App\Http\Requests;

use App\Rules\NotOwnDescendantRule;
use App\Rules\TenantExistsRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RoleUpdateRequest extends FormRequest
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
        $roleHasParent = (bool) $this->route('role')->parent_id;

        return [
            'name' => ['bail', 'required', 'string'],
            'permissions' => 'array',
            'permissions.*' => 'integer|exists:permissions,id',
            'parent_id' => [
                'bail',
                Rule::requiredIf($roleHasParent),
                Rule::when(
                    $roleHasParent,
                    [new TenantExistsRule('roles'), new NotOwnDescendantRule($this->route('role'))]
                ),
            ],
        ];
    }

    /**
     * Get the custom validation messages.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'The Name field is required.',
            'parent_id.required' => 'The Parent field is required.',
        ];
    }
}
