<?php

namespace App\Http\Requests;

use App\Enums\SpaRulesEnum;
use Illuminate\Foundation\Http\FormRequest;

class SpaUpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    private $requiredEntity2 = [SpaRulesEnum::AccountProduct->value, SpaRulesEnum::AccountSupplier->value, SpaRulesEnum::SupplierProduct->value];

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {

        return [
            'rule_id' => 'exists:spa_rules,id',
            'field_1' => ['required', 'numeric'],
            'field_2' => 'numeric',
            'operation_id' => 'exists:spa_operations,id',
            'price_plan_id' => 'nullable|exists:price_plans,id',
            'group_id' => 'nullable|exists:groups,id',
            'value' => 'numeric',
            'percentage' => 'numeric|min:0|max:100',
        ];
    }

    public function messages()
    {
        return [
            'field_1.required' => 'The first Entity is required',
            'field_2.required' => 'The second Entity is required',
            'field_1.unique' => 'There is already a SPA for that entity',
            'field_2.unique' => 'There is already a SPA for the combination of that entities',
        ];
    }
}
