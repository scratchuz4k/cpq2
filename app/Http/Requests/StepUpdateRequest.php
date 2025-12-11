<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class StepUpdateRequest extends FormRequest
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
        $step_id = null;
        if (array_key_exists('step', $this->route()->parameters())) {
            $step_id = $this->route()->parameters()['step']->id;
        }

        $user = Auth::user();

        return [
            'name' => [
                'string',
                'max:255',
                Rule::unique('steps', 'name')
                    ->ignore($step_id)
                    ->where(fn ($q) => $q
                        ->where('tenant_id', auth()->user()->tenant_id)
                        ->where('solution_type_id', $this->input('solution_type_id'))
                    ),
            ],
            'solution_type_id' => 'exists:solution_type_tenant,solution_type_id',
        ];
    }
}
