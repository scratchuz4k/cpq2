<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ImportPreviewRequest extends FormRequest
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
            'file' => 'required|file|mimetypes:text/csv,application/csv,text/plain|extensions:csv|max:2048',
            'overwrite' => 'required|boolean',
            'model' => [
                'required',
                'string',
                Rule::in(['Product', 'User']),
            ],
        ];
    }

    public function messages(): array
    {
        $message = 'The file to upload should be a valid CSV file.';

        return [
            'file.mimetypes' => $message,
            'file.extensions' => $message,
            'file.max' => 'The maximum file upload size is 2 MB.',
        ];
    }
}
