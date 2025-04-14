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

        return $solution_type;
    }

    public function update(SolutionType $solution_type, array $array): SolutionType
    {
        if (array_key_exists('configurations', $array) && is_array($array['configurations'])) {
            $solution_type->syncConfigurations($array['configurations']);
        }

        $solution_type->tenants()->updateExistingPivot(Auth::user()->tenant_id, [
            'updated_at' => now(),
        ]);

        if (array_key_exists('categories', $array)) {
            $solution_type->categories()->sync($array['categories']);
        }

        if (array_key_exists('tables', $array)) {
            $olderItems = collect($solution_type->tables);
            foreach ($array['tables'] as $table) {
                $table = $solution_type->tables()->updateOrCreate([
                    'code' => $table['code'],
                ], [
                    'group' => isset($table['group']) ? $table['group'] : null,
                    'group_name' => isset($table['group_name']) ? $table['group_name'] : null,
                    'columns' => $table['columns'],
                ]);
                $olderItems = $olderItems->reject(function ($temp) use ($table) {
                    return $temp->id === $table->id;
                });
            }
        }

        if (
            array_key_exists('configurations', $array)
            && is_array($array['configurations'])
        ) {
            $solution_type->syncConfigurations($array['configurations']);
        }

        return $solution_type;
    }
}
