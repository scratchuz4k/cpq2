<?php

namespace App\Http\Requests;

use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Http\FormRequest;

class OrderCreateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }


    public function prepareForValidation()
    {
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'quote_id' => 'required|exists:quotes,id',
            'account_id' => 'required|exists:accounts,id',
            'solution_type_id' => 'required|exists:solution_types,id',
            'margin' => 'numeric|min:0|max:100',
            'discount' => 'numeric|min:0|max:100',
            'items' => 'nullable|array',
            'items.*.quantity' => 'required|numeric',
            'items.*.name' => 'string',
            'items.*.raw_price' => 'numeric',
            'items.*.raw_price_monthly' => 'numeric',
            'items.*.price' => 'numeric',
            'items.*.price_monthly' => 'numeric',
            'items.*.margin' => 'numeric|min:0|max:100',
            'items.*.discount' => 'numeric|min:0|max:100',
        ];
    }
}
