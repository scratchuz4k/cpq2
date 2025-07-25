<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PriceUpdateRequest extends FormRequest
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
        $price_id = null;
        if (array_key_exists('price', $this->route()->parameters())) {
            $price_id = $this->route()->parameters()['price']->id;
        }

        return [
            'code' => [
                'numeric',
                Rule::unique('prices')
                    ->where(fn ($query) => $query->where('product_id', $this->input('product_id')))
                    ->ignore($price_id),
            ],
            'tax_rate' => 'required|numeric',
            'net' => 'required|numeric',
            'product_id' => 'exists:products,id',
        ];
    }
}
