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

        if (array_key_exists('products', $array) && is_array($array['products'])) {
            $step->products()->sync($array['products']);
        }

        return $step->refresh();
    }
}
