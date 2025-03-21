<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

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
        if (array_key_exists('category', $this->route()->parameters()))
            $category_id = $this->route()->parameters()['category']->id;

        return [
            'name' => 'required|string|max:255',
            'code' => [
                'required',
                Rule::unique('categories')->ignore($category_id),
            ],
        ];
    }
}
