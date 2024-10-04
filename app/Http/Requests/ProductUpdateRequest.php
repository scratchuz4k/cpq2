<?php

namespace App\Http\Requests;

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
        return [
            "tax_rate" => "required|number",
            "net" => "required|number",
            "tax" => "required|number",
            "gross" => "required|number",
            "spa_id" => "required|exists:spas,id",
            "product_id" => "required|exists:products,id",
        ];
    }
}
