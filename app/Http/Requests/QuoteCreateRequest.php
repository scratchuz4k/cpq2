<?php

namespace App\Http\Requests;

use App\Enums\QuoteStatusesEnum;
use App\Rules\SelectableAccount;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class QuoteCreateRequest extends FormRequest
{
    protected $authorized_statues = [
        QuoteStatusesEnum::CONFIGURE->name,
    ];

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    public function prepareForValidation()
    {
        $this->merge(['user_id' => Auth::id()]);
        $this->merge(['commission_rate' => Auth::user()->commission_rate]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'user_id' => 'required|exists:users,id',
            'account_id' => ['required', 'exists:accounts,id', new SelectableAccount],
            'solution_type_id' => 'required|exists:solution_types,id',
            'items' => 'nullable|array',
            'items.*.quantity' => 'required|numeric',
            'items.*.name' => 'string',
            'items.*.raw_price' => 'numeric',
            'items.*.price' => 'numeric',
            'items.*.discount' => 'numeric|min:0|max:100',
            'items.*.price_plan_id' => 'nullable|numeric|exists:price_plans,id',
            'items.*.renewal_terms_value' => 'nullable|numeric|min:1',
            'items.*.renewal_terms_unit' => 'nullable|string|in:DAYS,MONTHS,YEARS',
            'items.*.billing_frequency_value' => 'nullable|numeric|min:1',
            'items.*.billing_frequency_unit' => 'nullable|string|in:DAYS,MONTHS,YEARS',
            'status' => 'in:'.implode(',', $this->authorized_statues),
        ];
    }
}
