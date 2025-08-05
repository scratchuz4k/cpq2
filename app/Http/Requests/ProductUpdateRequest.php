<?php

namespace App\Http\Requests;

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
            'sku' => ['required', 'string', 'max:255',
                Rule::unique('products')->where(fn ($query) => $query->where('tenant_id', $user->tenant_id)
                    ->where('sku', request('sku')))->ignore($product_id),
            ],
            'name' => 'required|string|max:255',
            'supplier_id' => 'nullable|exists:suppliers,id',
            'category_id' => 'nullable|exists:categories,id',
            'brand_id' => 'nullable|exists:brands,id',
            'price_plan_id' => 'required|exists:price_plans,id',
        ];
    }
}
