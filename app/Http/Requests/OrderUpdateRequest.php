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
            'items' => 'nullable|array',
            'items.*.quantity' => 'required|numeric',
            'items.*.name' => 'required|string',
        ];
    }
}
