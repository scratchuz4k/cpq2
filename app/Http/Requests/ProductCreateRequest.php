<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProductCreateRequest extends FormRequest
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
            'code' => ['required', 'string', 'max:255', Rule::unique('products')],
            'name' => 'required|string|max:255',
            'supplier_id' => 'nullable|exists:suppliers,id',
            'brand_id' => 'nullable|exists:brands,id',
            'price_plan_id' => 'required|exists:price_plans,id',
            'prices.*.code' => [
                'required',
                'numeric',
                Rule::unique('prices')
                    ->where(fn ($query) => $query->where('product_id', $this->input('product_id'))),
            ],
            'prices.*.tax_rate' => 'required|numeric',
            'prices.*.net' => 'required|numeric',
        ];
    }
}
