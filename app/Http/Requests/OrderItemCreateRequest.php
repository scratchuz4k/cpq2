<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class OrderItemCreateRequest extends FormRequest
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
            "order_id" => "required",
            "name" => "required",
            "quantity" => "required|numeric",
            "raw_price" => "numeric",
            "raw_price_monthly" => "numeric",
            "price" => "numeric",
            "price_monthly" => "numeric",
            "margin" => "numeric|min:0|max:100",
            "discount" => "numeric|min:0|max:100",
        ];
    }
}
