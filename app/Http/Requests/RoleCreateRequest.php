<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class RoleCreateRequest extends FormRequest
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
        return [
            'name' => ['required', 'string',
                Rule::unique('roles')->where('tenant_id', Auth::user()->tenant_id)->where('guard_name', 'web')],
            'parent_id' => ['exists:roles,id'],
            'permissions' => 'array',
            'permissions.*' => 'integer|exists:permissions,id',
        ];
    }
}
