<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

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
        $product_id = null;
        if (array_key_exists('product', $this->route()->parameters()))
            $product_id = $this->route()->parameters()['product']->id;

        return [
            "code" => ["required", "string", "max:255", Rule::unique('products')->ignore($product_id )],
            "name" => "required|string|max:255",
            "supplier_id" => "nullable|exists:suppliers,id",
            "brand_id" => "nullable|exists:brands,id",
            "price_plan_id" => "required|exists:price_plans,id"
        ];
    }
}
