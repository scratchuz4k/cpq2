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
        $flattenedData = collect(request()->all())
            ->map(fn($value) => is_array($value) && array_filter($value) == [] ? null : $value) // Remove empty array values
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
            'bearer.value' => ['nullable', 'required_with:bearer.operator,bearer.unit', function ($attribute, $value, $fail) {
                if (is_array($value) && collect($value)->contains(fn($item) => empty($item))) {
                    $fail('All items in the Bearer must be filled.');
                }
            }],
            'bearer.unit' => 'nullable|required_with:bearer.operator,bearer.value',

            'speed.operator' => 'nullable|required_with:speed.value,speed.unit',
            'speed.value' => ['nullable', 'required_with:speed.operator,speed.unit', function ($attribute, $value, $fail) {
                if (is_array($value) && collect($value)->contains(fn($item) => empty($item))) {
                    $fail('All items in the Bearer must be filled.');
                }
            }],
            'speed.unit' => 'nullable|required_with:speed.operator,speed.value',

            'contractTermMonths' => 'nullable'
        ];
    }

    /**
     * Get the custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'no_field_filled.required' => 'You must fill at least one field.',
            'bearer.operator.required_with' => 'The bearer operator field is required when bearer unit and value are filled.',
            'bearer.unit.required_with' => 'The bearer unit field is required when bearer operator and value are filled.',
            'bearer.value.required_with' => 'The bearer value field is required when bearer operator and unit are filled.',


            'speed.operator.required_with' => 'The speed operator field is required when speed unit and value are filled.',
            'speed.unit.required_with' => 'The speed unit field is required when speed operator and value are filled.',
            'speed.value.required_with' => 'The speed value field is required when speed operator and unit are filled.',
        ];
    }
}
