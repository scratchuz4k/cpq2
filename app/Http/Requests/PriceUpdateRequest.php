<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PriceUpdateRequest extends FormRequest
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
            "code" => "required|numeric",
            "tax_rate" => "required|numeric",
            "net" => "required|numeric",
            "tax" => "required|numeric",
            "gross" => "required|numeric",
            "spa_id" => "required|exists:spas,id",
            "product_id" => "required|exists:products,id",
        ];
    }
}
