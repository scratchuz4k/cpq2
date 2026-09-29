<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class QuoteDiscountCreateRequest extends FormRequest
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
            'code' => 'required',
            'entity' => 'required|in:price_plans,quote',
            'entity_id' => 'required|numeric',
            'discount' => 'required|numeric|min:0|max:100',
            // Recurring discount for a single billing frequency (Annual, Monthly, Daily)
            'billing_frequency_unit' => 'nullable|string|in:DAYS,MONTHS,YEARS',
        ];
    }
}
