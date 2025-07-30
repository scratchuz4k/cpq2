<?php

namespace App\Http\Requests;

use App\Enums\OrderStatusesEnum;
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

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'quote_id' => 'exists:quotes,id',
            'user_id' => 'exists:users,id',
            'account_id' => 'exists:accounts,id',
            'solution_type_id' => 'exists:solution_types,id',
            'margin' => 'numeric|min:0|max:100',
            'discount' => 'numeric|min:0|max:100',
            'items' => 'array',
            'items.*.quantity' => 'required|numeric',
            'items.*.name' => 'string',
            'items.*.raw_price' => 'numeric',
            'items.*.raw_price_monthly' => 'numeric',
            'items.*.price' => 'numeric',
            'items.*.price_monthly' => 'numeric',
            'items.*.margin' => 'numeric|min:0|max:100',
            'items.*.discount' => 'numeric|min:0|max:100',
            'status' => 'in:'.implode(',', OrderStatusesEnum::names()),
        ];
    }
}
