<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class IndexRequest extends FormRequest
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
            'order' => 'nullable|in:asc,desc',
            'orderBy' => 'nullable',
            'page' => 'nullable|numeric',
            'limit' => 'nullable|numeric',
            'search' => 'nullable|string',
            'filters' => 'nullable|array',
            'filters.*.column' => 'required|string',
            'filters.*.operator' => 'required|in:not_equals,equals,not_contains,contains,starts_with,ends_with,greater_than,less_than,greater_than_or_equal_to,less_than_or_equal_to,is_null,in,search',
            'filters.*.value' => 'required',
            'filters.*.function' => 'nullable|in:where,orWhere',
        ];
    }
}
