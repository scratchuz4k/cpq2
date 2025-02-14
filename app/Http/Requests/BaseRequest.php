<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BaseRequest extends FormRequest
{
    public function messages(): array
    {
        return [
            'solution_type_id.unique' => 'This solution type is already assigned to this tenant.',
        ];
    }
}
