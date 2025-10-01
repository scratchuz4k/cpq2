<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class CategoryUpdateRequest extends FormRequest
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
        $category_id = null;
        if (array_key_exists('category', $this->route()->parameters())) {
            $category_id = $this->route()->parameters()['category']->id;
        }

        $user = Auth::user();

        return [
            'name' => 'string|max:255',
            'code' => ['string', 'max:255',
                Rule::unique('categories')->where(fn ($query) => $query->where('tenant_id', $user->tenant_id)
                    ->where('code', request('code')))->ignore($category_id),
            ],
            'solution_type_id' => 'exists:solution_type_tenant,id',
        ];
    }
}
