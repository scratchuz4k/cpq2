<?php

namespace App\Http\Requests;

use App\Enums\QuoteStatusesEnum;
use Illuminate\Foundation\Http\FormRequest;

class QuoteUpdateRequest extends FormRequest
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
            'user_id' => 'exists:users,id',
            'account_id' => 'exists:accounts,id',
            'solution_type_id' => 'exists:solution_types,id',
            'discounts' => 'array|nullable',
            'discounts.*.code' => 'required',
            'discounts.*.entity' => 'required|in:price_plans',
            'discounts.*.value' => 'required|numeric',
            'items' => 'array',
            'items.*.quantity' => 'required|numeric',
            'items.*.name' => 'string',
            'items.*.raw_price' => 'numeric',
            'items.*.price' => 'numeric',
            'items.*.discount' => 'numeric|min:0|max:100',
            'items.*.price_plan_id' => 'numeric|exists:price_plans,id',
            'items.*.contract_length' => 'numeric|min:1',
            'status' => 'in:'.implode(',', QuoteStatusesEnum::names()),
        ];
    }
}
