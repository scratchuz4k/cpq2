<?php

namespace App\Http\Requests;

use App\Enums\ProductStatusesEnum;
use App\Models\Product;
use App\Rules\TenantExistsRule;
use App\Rules\TenantUniqueRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProductCreateRequest extends FormRequest
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
        $statuses = array_map(
            static fn ($case) => $case->value,
            ProductStatusesEnum::cases()
        );

        return [
            'sku' => [
                'required',
                'string',
                'max:255',
                new TenantUniqueRule('products', 'sku'),
            ],
            'name' => 'required|string|max:255',
            'supplier_id' => [
                'nullable',
                'numeric',
                new TenantExistsRule('suppliers'),
            ],
            'price_plan_id' => [
                'required',
                'numeric',
                Rule::exists('price_plans', 'id'),
            ],
            'steps' => [
                'array', 'nullable',
            ],
            'steps.*' => [
                'integer',
                new TenantExistsRule('steps'),
            ],
            'categories.*' => [
                'integer',
                new TenantExistsRule('categories'),
            ],
            'status' => [
                'nullable',
                'string',
                Rule::in($statuses),
            ],
            'prices.*.code' => [
                'nullable',
                'numeric',
                Rule::unique('prices')
                    ->where(fn ($query) => $query->where('product_id', $this->input('product_id'))),
            ],
            'prices.*.cost' => 'required|numeric',
            'prices.*.net' => 'required|numeric',
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
                $this->validateStepLinks(null, $stepIds, $validator);
            }
        });
    }

    private function validateStepLinks(?Product $product, array $stepIds, $validator): void
    {
        $linkedProductIds = collect();
        if ($product) {
            $linkedProductIds = $product->children()->pluck('products.id')
                ->merge($product->parents()->pluck('products.id'));
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
