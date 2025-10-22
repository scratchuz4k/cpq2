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

    public function prepareForValidation()
    {
        // Example: Set a default value if 'tenant_id' is not present
        if ($this->has('filters') && is_string($this->filters)) {
            $this->merge([
                'filters' => json_decode($this->filters, true),
            ]);
        }
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
            'group' => 'nullable',
            'groupOrder' => 'nullable',
            'page' => 'nullable|numeric',
            'limit' => 'nullable|numeric',
            'search' => 'nullable|string',
            'filters' => 'nullable|array',
            'filters.*.group' => 'nullable|array',
            'filters.*.column' => 'required_without:filters.*.group|string',
            'filters.*.operator' => 'required_without:filters.*.group|in:not_equals,equals,not_contains,contains,starts_with,ends_with,greater_than,less_than,greater_than_or_equal_to,less_than_or_equal_to,is_null,in,search,not_in',
            'filters.*.value' => 'nullable',
            'filters.*.function' => 'nullable|in:where,orWhere',
            'filters.*.group.*.column' => 'required|string',
            'filters.*.group.*.operator' => 'required|in:not_equals,equals,not_contains,contains,starts_with,ends_with,greater_than,less_than,greater_than_or_equal_to,less_than_or_equal_to,is_null,in,search,not_in',
            'filters.*.group.*.value' => 'nullable',
            'filters.*.group.*.function' => 'nullable|in:where,orWhere',
        ];
    }
}
