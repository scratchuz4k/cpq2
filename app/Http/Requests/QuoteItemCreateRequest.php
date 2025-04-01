<?php

namespace App\Http\Requests;

use App\Models\PricePlan;
use App\Models\QuoteItem;
use Illuminate\Foundation\Http\FormRequest;

class QuoteItemCreateRequest extends FormRequest
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
        if (! $this->has('price_plan_id')) {
            $this->merge(['price_plan_id' => PricePlan::where('code', strtoupper('single'))->value('id')]);
        }

        if ($this->has('quote_id') && (! $this->has('group') || is_null($this->group))) {
            $item = QuoteItem::where('quote_id', $this->quote_id)->orderBy('group', 'desc')->first();
            $this->merge(['group' => $item ? $item->group + 1 : 0]);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'quote_id' => 'required',
            'name' => 'required',
            'quantity' => 'required|numeric|min:1',
            'group' => 'numeric',
            'raw_price' => 'numeric',
            'price' => 'numeric',
            'margin' => 'numeric|min:0|max:100',
            'discount' => 'numeric|min:0|max:100',
            'price_plan_id' => 'required|exists:price_plans,id',
            'contract_length' => 'numeric|min:1',
        ];
    }
}
