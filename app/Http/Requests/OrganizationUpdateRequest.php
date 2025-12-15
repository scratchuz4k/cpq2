<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class OrganizationUpdateRequest extends FormRequest
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
        $org_id = null;
        if ($this->route() && array_key_exists('organization', $this->route()->parameters())) {
            $org_id = $this->route()->parameters()['organization']->id;
        }

        return [
            'name' => [
                Rule::unique('accounts')->where(fn ($query) => $query->where('tenant_id', $user->tenant_id)
                    ->where('name', request('name')))->ignore($org_id),
            ],
            'email' => 'email',
        ];

    }
}
