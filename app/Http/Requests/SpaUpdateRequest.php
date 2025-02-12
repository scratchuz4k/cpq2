<?php

namespace App\Http\Requests;

use App\Enums\SpaRulesEnum;
use App\Models\SpaRule;
use Illuminate\Validation\Rule;
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

    private $requiredEntity2 = [SpaRulesEnum::AccountProduct->value, SpaRulesEnum::AccountSupplier->value, SpaRulesEnum::SupplierProduct->value,];

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $spa_id = null;
        if (array_key_exists('spa', $this->route()->parameters()))
            $spa_id = $this->route()->parameters()['spa']->id;

        $rules =  ["numeric"];
        $rule = SpaRule::find($this->input('rule_id'));
        if (isset($rule) && in_array($rule->class, $this->requiredEntity2))
            $rules[] = "required";


        return [
            "rule_id" => "exists:spa_rules,id",
            "field_1" => [
                "required",
                "numeric",
                Rule::unique('spas')
                    ->where(function ($query) {
                        return $query->where('rule_id', $this->input('rule_id'))
                            ->where('field_2', $this->input('field_2', 0));
                    })
                    ->ignore($spa_id), // Dynamically get the ID from the route
            ],
            "field_2" => $rules,
            "operation_id" => "exists:spa_operations,id",
            "value" => "numeric",
            "percentage" => "numeric|min:0|max:100",
        ];
    }
    public function messages()
    {
        return [
            "field_1.required" => "The first Entity is required",
            "field_2.required" => "The second Entity is required",
            "field_1.unique" => "There is already a SPA for that entity",
            "field_2.unique" => "There is already a SPA for the combination of that entities",
        ];
    }
}
