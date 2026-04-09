<?php

namespace App\Services;

use App\Models\Step;

class StepService
{
    public function store(array $array): Step
    {
        $step = Step::create($array);

        return $step;
    }

    public function update(Step $step, array $array): Step
    {
        $step->update($array);

        if (array_key_exists('product_id', $array)) {
            $productId = $array['product_id'];

            $hasLinkedProduct = $step->products()
                ->where(function ($query) use ($productId) {
                    $query->whereHas('children', fn ($q) => $q->where('products.id', $productId));
                })
                ->exists();

            if ($hasLinkedProduct) {
                throw new \Exception('Cannot add product: it is already linked to an existing product in this step.');
            }

            $step->products()->toggle($productId);
        }

        return $step->refresh();
    }
}
