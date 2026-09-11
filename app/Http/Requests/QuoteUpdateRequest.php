<?php

namespace App\Http\Requests;

use App\Enums\QuoteStatusesEnum;
use App\Rules\SelectableAccount;
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
            'account_id' => ['sometimes', 'exists:accounts,id', new SelectableAccount],
            'solution_type_id' => 'exists:solution_types,id',
            'items' => 'array',
            'items.*.quantity' => 'required|numeric',
            'items.*.name' => 'string',
            'items.*.raw_price' => 'numeric',
            'items.*.price' => 'numeric',
            'items.*.discount' => 'numeric|min:0|max:100',
            'items.*.price_plan_id' => 'numeric|exists:price_plans,id',
            'items.*.renewal_terms_value' => 'nullable|numeric|min:1',
            'items.*.renewal_terms_unit' => 'nullable|string|in:DAYS,MONTHS,YEARS',
            'items.*.billing_frequency_value' => 'nullable|numeric|min:1',
            'items.*.billing_frequency_unit' => 'nullable|string|in:DAYS,MONTHS,YEARS',
            'status' => 'in:'.implode(',', QuoteStatusesEnum::names()),
        ];
    }
}
