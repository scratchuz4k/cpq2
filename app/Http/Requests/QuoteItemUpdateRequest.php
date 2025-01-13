<?php

namespace App\Http\Requests;

use App\Models\PricePlan;
use Illuminate\Foundation\Http\FormRequest;

class QuoteItemUpdateRequest extends FormRequest
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
            "quote_id" => "required",
            "name" => "required",
            "quantity" => "required|numeric",
            "raw_price" => "numeric",
            "price" => "numeric",
            "discount" => "numeric|min:0|max:100",
            "price_plan_id" => "nullable|numeric|exists:price_plans,id",
            "contract_length" => "numeric|min:1",
        ];
    }
}
