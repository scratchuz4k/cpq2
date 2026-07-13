<?php

namespace App\Http\Requests;

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
            'name' => 'string',
            'permissions' => 'array',
            'permissions.*' => 'integer|exists:permissions,id',
            'parent_id' => [
                Rule::requiredIf($roleHasParent),
                Rule::when(
                    $roleHasParent,
                    [new TenantExistsRule('roles')]
                ),
            ],
        ];
    }
}
