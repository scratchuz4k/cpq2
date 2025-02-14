<?php

namespace App\Services;

use App\Models\SolutionType;
use Illuminate\Support\Facades\Auth;

class SolutionTypeService
{

    public function store(array $array)
    {
        $user = Auth::user();
        $solution_type = SolutionType::findOrFail($array['solution_type_id']);

        $solution_type->tenants()->attach($user->tenant_id);

        if (
            array_key_exists('configurations', $array)
            && is_array($array['configurations'])
        ) {
            $solution_type->syncConfigurations($array['configurations']);
        }

        return $solution_type;
    }

    public function update(SolutionType $solution_type, array $array): SolutionType
    {
        if (array_key_exists('configurations', $array) && is_array($array['configurations'])) {
            $solution_type->syncConfigurations($array['configurations']);
        }

        return $solution_type;
    }
}
