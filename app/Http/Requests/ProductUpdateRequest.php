<?php

namespace App\Http\Requests;

use App\Models\Product;
use App\Rules\TenantExistsRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class ProductUpdateRequest extends FormRequest
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
        $user = Auth::user();
        $product_id = null;
        if (array_key_exists('product', $this->route()->parameters())) {
            $product_id = $this->route()->parameters()['product']->id;
        }

        $user = Auth::user();

        return [
            'sku' => ['string', 'max:255',
                Rule::unique('products')->where(fn ($query) => $query->where('tenant_id', $user->tenant_id)
                    ->where('sku', request('sku')))->ignore($product_id),
            ],
            'name' => 'string|max:255',
            'categories.*' => [
                'integer',
                new TenantExistsRule('categories'),
            ],
            'steps' => [
                'array', 'nullable',
            ],
            'steps.*' => [
                'integer',
                new TenantExistsRule('steps'),
            ],
            'price_plan_id' => 'exists:price_plans,id',
            'supplier_id' => 'nullable|exists:suppliers,id',
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
