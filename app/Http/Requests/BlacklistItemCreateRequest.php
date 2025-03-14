<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BlacklistItemCreateRequest extends FormRequest
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
            'productName' => 'nullable',
            'providerName' => 'nullable',
            'connectivitySupplierGroup' => 'nullable',

            'bearer.operator' => 'nullable|required_with:bearer.value,bearer.unit',
            'bearer.value' => 'nullable|required_with:bearer.operator,bearer.unit',
            'bearer.unit' => 'nullable|required_with:bearer.operator,bearer.value',

            'speed.operator' => 'nullable|required_with:speed.value,speed.unit',
            'speed.value' => 'nullable|required_with:speed.operator,speed.unit',
            'speed.unit' => 'nullable|required_with:speed.operator,speed.value',

            'contractTermMonths' => 'nullable'
        ];
    }
}
