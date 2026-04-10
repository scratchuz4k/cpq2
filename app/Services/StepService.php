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

        return $step->refresh();
    }
}
