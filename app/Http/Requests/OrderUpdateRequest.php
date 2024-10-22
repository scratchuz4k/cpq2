<?php

namespace App\Http\Requests;

use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Http\FormRequest;

class OrderUpdateRequest extends FormRequest
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
        $this->merge(['user_id' => Auth::id()]);
    }


    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'user_id' => 'nullable|exists:users,id',
            'account_id' => 'nullable|exists:accounts,id',
            'solution_type_id' => 'nullable|exists:solution_types,id',
            'margin' => 'nullable|numeric|min:0|max:100',
            'discount' => 'nullable|numeric|min:0|max:100',
            'items' => 'nullable|array',
            'items.*.quantity' => 'required|numeric',
            'items.*.name' => 'required|string',
            'items.*.price' => 'required|numeric',
            'items.*.price_monthly' => 'nullable|numeric',
            'items.*.margin' => 'nullable|numeric|min:0|max:100',
            'items.*.discount' => 'nullable|numeric|min:0|max:100',
        ];
    }
}
