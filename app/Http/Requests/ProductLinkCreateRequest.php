<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Validator;

class ProductLinkCreateRequest extends FormRequest
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
            'product_id' => 'required|integer|exists:products,id',
            'child_id' => 'required|integer|exists:products,id|different:product_id',
        ];
    }

    /**
     * Add cross-field validation: child must not share a step with the parent.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $parentId = $this->input('product_id');
            $childId = $this->input('child_id');

            if (! $parentId || ! $childId) {
                return;
            }

            $parentStepIds = DB::table('product_step')
                ->where('product_id', $parentId)
                ->pluck('step_id');

            if ($parentStepIds->isEmpty()) {
                return;
            }

            $sharesStep = DB::table('product_step')
                ->where('product_id', $childId)
                ->whereIn('step_id', $parentStepIds)
                ->exists();

            if ($sharesStep) {
                $validator->errors()->add(
                    'child_id',
                    'The child product cannot be added to the same step as the parent product.'
                );
            }

            $alreadyExists = DB::table('product_links')
                ->where('product_id', $parentId)
                ->where('child_id', $childId)
                ->exists();

            if ($alreadyExists) {
                $validator->errors()->add(
                    'child_id',
                    'This product link already exists.'
                );
            }
        });
    }
}
