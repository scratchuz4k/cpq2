<?php

namespace App\Http\Requests;

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
            'field_1' => 'required|numeric',
            'field_2' => 'numeric',
            'operation_id' => 'required|exists:spa_operations,id',
            "value" => 'numeric',
            "percentage" => 'numeric|min:0|max:100',
        ];
    }
}
