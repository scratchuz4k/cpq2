<?php

namespace App\Http\Requests;

use App\Trait\ValidationMessages;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SolutionTypeCreateRequest extends FormRequest
{

    use ValidationMessages;

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
            'tenant_id' => 'required|exists:tenants,id',
            'solution_type_id' => [
                'required',
                'exists:solution_types,id',
                Rule::unique('solution_type_tenant')
                    ->where(
                        'tenant_id',
                        $this->input('tenant_id')
                    )
            ],
            'configurations' => 'nullable|array',
        ];
    }
}
