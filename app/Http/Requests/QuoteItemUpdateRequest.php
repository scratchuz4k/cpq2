<?php

namespace App\Http\Requests;

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
            'name' => 'nullable',
            'quantity' => 'required|numeric',
            'group' => 'numeric',
            'raw_price' => 'numeric',
            'price' => 'numeric',
            'discount' => 'numeric|min:0|max:100',
            'price_plan_id' => 'numeric|exists:price_plans,id',
            'renewal_terms_value' => 'nullable|numeric|min:1',
            'renewal_terms_unit' => 'nullable|string|in:DAYS,MONTHS,YEARS',
            'billing_frequency_value' => 'nullable|numeric|min:1',
            'billing_frequency_unit' => 'nullable|string|in:DAYS,MONTHS,YEARS',
        ];
    }
}
