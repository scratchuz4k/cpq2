<?php

namespace App\Http\Requests;

use App\Models\SpaRule;
use App\Enums\SpaRulesEnum;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class SolutionTypeUpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }
    private $requiredEntity2 = [SpaRulesEnum::AccountProduct->value, SpaRulesEnum::AccountSupplier->value, SpaRulesEnum::SupplierProduct->value,];

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [

        ];
    }
}
