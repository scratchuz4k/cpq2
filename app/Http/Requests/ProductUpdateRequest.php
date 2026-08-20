<?php

namespace App\Http\Requests;

use App\Enums\FrequencyUnitsEnum;
use App\Models\Product;
use App\Rules\TenantExistsRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class ProductUpdateRequest extends FormRequest
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
        $user = Auth::user();
        $product_id = null;
        if (array_key_exists('product', $this->route()->parameters())) {
            $product_id = $this->route()->parameters()['product']->id;
        }

        $user = Auth::user();

        return [
            'sku' => ['string', 'max:255',
                Rule::unique('products')->where(fn ($query) => $query->where('tenant_id', $user->tenant_id)
                    ->where('sku', request('sku')))->ignore($product_id),
            ],
            'name' => 'string|max:255',
            'categories.*' => [
                'integer',
                new TenantExistsRule('categories'),
            ],
            'price_plan_id' => 'exists:price_plans,id',
            'contract_term_value' => [
                Rule::requiredIf($this->input('price_plan_id') == 1),
                'nullable',
                'integer',
                'between:1,100',
            ],
            'contract_term_unit' => [
                Rule::requiredIf($this->input('price_plan_id') == 1),
                'nullable',
                Rule::in(FrequencyUnitsEnum::names()),
            ],
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $parentId = $this->input('parent_id');
            $categoryId = $this->input('category_id');

            if ($parentId) {
                $parent = Product::find($parentId);

                if ($parent && $parent->category_id === (int) $categoryId) {
                    $validator->errors()->add(
                        'parent_id',
                        'The parent product cannot belong to the same category as the product.'
                    );
                }
            }

            $stepIds = $this->input('steps', []);
            if (! empty($stepIds)) {
                $product = $this->route('product');
                $this->validateStepLinks($product, $stepIds, $validator);
            }
        });
    }

    private function validateStepLinks(?Product $product, array $stepIds, $validator): void
    {
        $linkedProductIds = collect();
        if ($product) {
            $linkedProductIds = $product->linkedChildren->pluck('products.id')
                ->merge($product->linkedParents->pluck('products.id'));
        }

        if ($linkedProductIds->isEmpty()) {
            return;
        }

        $conflictingStep = \App\Models\Step::whereIn('id', $stepIds)
            ->whereHas('products', fn ($q) => $q->whereIn('products.id', $linkedProductIds))
            ->first();

        if ($conflictingStep) {
            $validator->errors()->add(
                'steps',
                "Cannot assign product to step \"{$conflictingStep->name}\": it already contains a linked product."
            );
        }
    }
}
