<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class SpaCreateRequest extends FormRequest
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
            'rule_id' => 'required|exists:spa_rules,id',
            'field_1' => [
                'required',
                'numeric',
                Rule::unique('spas')->where(function ($query) {
                    return $query
                        ->where('rule_id', $this->input('rule_id'));
                }),
            ],
            'field_2' => [
                'numeric',
                Rule::unique('spas')->where(function ($query) {
                    return $query
                        ->where('rule_id', $this->input('rule_id'))
                        ->where('field_1', $this->input('field_1'));
                }),
            ],
            'operation_id' => 'required|exists:spa_operations,id',
            "value" => 'numeric',
            "percentage" => 'numeric|min:0|max:100',
        ];
    }
    public function messages()
    {
        return [
            'field_1.unique' => 'There is alredy a SPA for that entity',
            'field_2.unique' => 'There is alredy a SPA for the combination of that entities',
        ];
    }
}
