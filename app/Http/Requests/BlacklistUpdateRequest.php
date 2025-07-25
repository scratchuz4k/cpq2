<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BlacklistUpdateRequest extends FormRequest
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
        $data = request()->only([
            'productName',
            'providerName',
            'connectivitySupplierGroup',
            'bearer',
            'speed',
            'contractTermMonths',
        ]);

        $flattenedData = collect($data)
            ->map(fn ($value) => is_array($value) && array_filter($value) == [] ? null : $value) // Remove empty array values
            ->all();

        if (empty(array_filter($flattenedData))) {
            return [
                'no_field_filled' => 'required',
            ];
        }

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

            'contractTermMonths' => 'nullable',
        ];
    }

    public function messages(): array
    {
        return [
            'no_field_filled.required' => 'You must fill at least one field.',
        ];
    }
}
