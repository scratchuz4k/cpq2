<?php

namespace App\Http\Requests;

use App\Models\Product;
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
            'sku' => ['required', 'string', 'max:255', Rule::unique('products')],
            'name' => 'required|string|max:255',
            'supplier_id' => 'nullable|exists:suppliers,id',
            'brand_id' => 'nullable|exists:brands,id',
            'price_plan_id' => 'required|exists:price_plans,id',
            'category_id' => 'required|exists:categories,id',
            'parent_id' => 'nullable|exists:products,id',
            'prices.*.code' => [
                'required',
                'numeric',
                Rule::unique('prices')
                    ->where(fn ($query) => $query->where('product_id', $this->input('product_id'))),
            ],
            'prices.*.cost' => 'required|numeric',
            'prices.*.net' => 'required|numeric',
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $parentId = $this->input('parent_id');
            $categoryId = $this->input('category_id');

            if ($parentId) {
                $parent = Product::find($parentId);

                if ($parent && $parent->category_id === (int) $categoryId) {
                    $validator->errors()->add(
                        'parent_id',
                        'The parent product cannot belong to the same category as the product.'
                    );
                }
            }
        });
    }
}
