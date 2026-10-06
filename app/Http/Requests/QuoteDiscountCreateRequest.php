<?php

namespace App\Http\Requests;

use App\Models\PricePlan;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

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
            // Recurring discounts always apply to a single billing frequency (Annual, Monthly, Daily)
            'billing_frequency_unit' => [
                Rule::requiredIf(fn () => $this->isRecurringPricePlanDiscount()),
                'nullable',
                'string',
                'in:DAYS,MONTHS,YEARS',
            ],
        ];
    }

    private function isRecurringPricePlanDiscount(): bool
    {
        return $this->input('entity') === 'price_plans'
            && PricePlan::whereKey($this->input('entity_id'))->value('code') === 'MONTHLY';
    }
}
